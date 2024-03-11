<?php

namespace MBO\RemoteGit;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\Exception\ClientNotFoundException;
use MBO\RemoteGit\Helper\LoggerHelper;
use MBO\RemoteGit\Http\TokenType;
use MBO\RemoteGit\Github\GithubClient;
use MBO\RemoteGit\Gitlab\GitlabClient;
use MBO\RemoteGit\Gogs\GogsClient;
use MBO\RemoteGit\Helper\ClientHelper;
use MBO\RemoteGit\Local\LocalClient;

/**
 * Helper to create clients according to URL.
 *
 * Note that it rely on a static interface on clients (TYPE and TOKEN_TYPE)
 *
 * @author mborne
 */
class ClientFactory
{
    /**
     * @var ClientFactory
     */
    private static $instance;

    /**
     * Associates client type to metadata ('className','tokenType')
     *
     * @var array<string,array<string,string>>
     */
    private $types = [];

    private function __construct()
    {
        $this->register(GitlabClient::class);
        $this->register(GithubClient::class);
        $this->register(GogsClient::class);
        $this->register(LocalClient::class);
    }

    /**
     * True if type is registred
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasType($type)
    {
        return isset($this->types[$type]);
    }

    /**
     * Get supported types
     *
     * @return string[]
     */
    public function getTypes()
    {
        return array_keys($this->types);
    }

    /**
     * Create a client with options
     */
    public static function createClient(
        ClientOptions $options,
        LoggerInterface $logger = null
    ): ClientInterface {
        return self::getInstance()->createGitClient($options, $logger);
    }

    /**
     * Create a client with options
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function createGitClient(
        ClientOptions $options,
        LoggerInterface $logger = null
    ): ClientInterface {
        $logger = LoggerHelper::handleNull($logger);

        /* Detect client type from URL if not specified */
        if (!$options->hasType()) {
            $className = self::detectClientClass($options->getUrl());
            $options->setType($className::TYPE);
            $logger->debug(sprintf(
                'Type %s found for %s',
                $options->getType(),
                $options->getUrl()
            ));
        }

        /* Ensure that type exists */
        if (!$this->hasType($options->getType())) {
            throw new ClientNotFoundException($options->getType(), $this->getTypes());
        }

        /* Handle LocalClient */
        if (LocalClient::TYPE === $options->getType()) {
            return new LocalClient($options->getUrl(), $logger);
        }

        /* Force github API URL */
        if (GithubClient::TYPE === $options->getType()) {
            $options->setUrl('https://api.github.com');
        }

        /* Retrieve type metadata */
        $metadata = $this->types[$options->getType()];
        $clientClass = $metadata['className'];
        $tokenType = $metadata['tokenType'];

        /* common http options */
        $guzzleOptions = [
            'base_uri' => $options->getUrl(),
            'timeout' => 60.0,
            'headers' => TokenType::createHttpHeaders(
                $tokenType,
                $options->getToken()
            ),
        ];
        /* disable SSL checks */
        if ($options->isUnsafeSsl()) {
            $guzzleOptions['verify'] = false;
        }

        /* create http client */
        $httpClient = new GuzzleHttpClient($guzzleOptions);
        /* create git client */
        $result = new $clientClass($httpClient, $logger);
        assert($result instanceof ClientInterface);

        return $result;
    }

    /**
     * Get client class according to URL content
     */
    public static function detectClientClass(string $url): string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'])) {
            return LocalClient::class;
        }

        $hostname = parse_url($url, PHP_URL_HOST);
        assert('string' === gettype($hostname));
        if ('api.github.com' === $hostname || 'github.com' === $hostname) {
            return GithubClient::class;
        } elseif (str_contains($hostname, 'gogs')) {
            return GogsClient::class;
        }
        /*
         * fallback to gitlab to ensure comptability with original version
         * of satis-gitlab
         */
        return GitlabClient::class;
    }

    /**
     * @return ClientFactory
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new ClientFactory();
        }

        return self::$instance;
    }

    /**
     * Register client type
     *
     * @param class-string $className
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function register(string $className): void
    {
        $clientProperties = ClientHelper::getStaticProperties($className);
        $this->types[$clientProperties['typeName']] = $clientProperties;
    }
}
