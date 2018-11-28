<?php

namespace MBO\RemoteGit;

use Psr\Log\LoggerInterface;
use \GuzzleHttp\Client as GuzzleHttpClient;

use MBO\RemoteGit\Helper\LoggerHelper;
use MBO\RemoteGit\Http\TokenType;

use MBO\RemoteGit\Github\GithubClient;
use MBO\RemoteGit\Gitlab\GitlabClient;
use MBO\RemoteGit\Gogs\GogsClient;


/**
 * Helper to create clients according to URL
 * 
 * @author mborne
 */
class ClientFactory {

    /**
     * @var ClientFactory
     */
    private static $instance ;

    /**
     * Associates client type to metadata ('className','tokenType')
     *
     * @var array
     */
    private $types = array();

    private function __construct(){
        $this->register(GitlabClient::class);
        $this->register(GithubClient::class);
        $this->register(GogsClient::class);
    }

    /**
     * True if type is registred
     *
     * @param string $type
     * @return boolean
     */
    public function hasType($type){
        return isset($this->types[$type]);
    }

    /**
     * Get supported types
     *
     * @return array()
     */
    public function getTypes(){
        return array_keys($this->types);
    }

    /**
     * Create a client with options
     *
     * @param ClientOptions $options
     * @param LoggerInterface $logger
     * @return ClientInterface
     */
    public static function createClient(
        ClientOptions $options,
        LoggerInterface $logger = null
    ) {
        return self::getInstance()->createGitClient($options,$logger);
    }

    /**
     * Create http client according to given options
     */
    public function createGitClient(
        ClientOptions $options,
        LoggerInterface $logger = null
    ){
        $logger = LoggerHelper::handleNull($logger);

        /* Detect client type from URL if not specified */
        if ( ! $options->hasType() ){
            $className = self::detectClientClass($options->getUrl());
            $options->setType( $className::TYPE ) ;
            $logger->debug(sprintf(
                'Type %s found for %s',
                $options->getType(),
                $options->getUrl()
            ));
        }

        /* Ensure that type exists */
        if ( ! $this->hasType($options->getType()) ){
            throw new \Exception(sprintf(
                "type '%s' not found in [%s]",
                $options->getType(),
                implode(',',$this->getTypes())
            ));
        }

        /* Force github API URL */
        if ( GithubClient::TYPE === $options->getType() ){
            $options->setUrl('https://api.github.com');
        }

        /* Retrieve type metadata */
        $metadata = $this->types[ $options->getType() ];
        $clientClass = $metadata['className'];
        $tokenType   = $metadata['tokenType'];
        
        /* common http options */
        $guzzleOptions = array(
            'base_uri' => $options->getUrl(),
            'timeout'  => 10.0,
            'headers' => TokenType::createHttpHeaders(
                $tokenType,
                $options->getToken()
            )
        );
        /* disable SSL checks */
        if ( $options->isUnsafeSsl() ){
            $guzzleOptions['verify'] = false;
        }

        /* create http client */
        $httpClient = new GuzzleHttpClient($guzzleOptions);
        /* create git client */
        return new $clientClass($httpClient,$logger);
    }


    /**
     * Get client class according to URL content
     *
     * @param string $url
     * @return string
     */
    public static function detectClientClass($url){
        $hostname = parse_url($url, PHP_URL_HOST);
        if ( 'api.github.com' === $hostname || 'github.com' === $hostname ){
            return GithubClient::class;
        }else if ( strpos($hostname, 'gogs') !== false ) {
            return GogsClient::class;
        } else {
            return GitlabClient::class;
        }
    }

    /**
     * @return ClientFactory
     */
    public static function getInstance(){
        if ( is_null(self::$instance) ){
            self::$instance = new ClientFactory();
        }
        return self::$instance;
    }

    /**
     * Register client type
     * @param string $clazzName
     * @return void
     */
    private function register($className){
        $reflectionClass = new \ReflectionClass($className);
        if ( ! $reflectionClass->implementsInterface(ClientInterface::class) ){
            throw new \Exception(sprintf(
                '%s must implement %s',
                $className,
                ClientInterface::class
            ));
        }
        /* retrieve TYPE */
        $type = $reflectionClass->getConstant('TYPE');
        if ( empty($type) ){
            throw new \Exception(sprintf(
                'Missing const TYPE on %s',
                $className
            ));
        }
        /* retrieve TOKEN_TYPE */        
        $tokenType = $reflectionClass->getConstant('TOKEN_TYPE');
        if ( empty($tokenType) ){
            throw new \Exception(sprintf(
                'Missing const TOKEN_TYPE on %s',
                $className
            ));
        }
        $this->types[$type] = array(
            'className' => $className,
            'tokenType' => $tokenType
        );
    }

}

