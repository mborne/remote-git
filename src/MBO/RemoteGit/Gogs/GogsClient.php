<?php

namespace MBO\RemoteGit\Gogs;

use Psr\Log\LoggerInterface;
use \GuzzleHttp\Client as GuzzleHttpClient;

use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Helper\LoggerHelper;


/**
 * 
 * Client implementation for gogs
 * 
 * @author mborne
 * 
 */
class GogsClient implements ClientInterface {

    const DEFAULT_PER_PAGE = 1000;

    /**
     * @var GuzzleHttpClient
     */
    protected $httpClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor with an http client and a logger
     * @param $httpClient http client
     * @param $logger
     */
    public function __construct(
        GuzzleHttpClient $httpClient,
        LoggerInterface $logger = null
    ){
        $this->httpClient = $httpClient ;
        $this->logger = LoggerHelper::handleNull($logger) ;
    }

    /*
     * @{inheritDoc}
     */    
    public function find(FindOptions $options){
        $result = array();
        if ( empty($options->getUsers()) && empty($options->getOrganizations()) ){
            return $this->findByCurrentUser(
                $options->getFilter()
            );
        }
        foreach ( $options->getUsers() as $user ){
            $result = array_merge($result,$this->findByUser(
                $user,
                $options->getFilter()
            ));
        }
        foreach ( $options->getOrganizations() as $org ){
            $result = array_merge($result,$this->findByOrg(
                $org,
                $options->getFilter()
            ));
        }
        return $result;
    }

    /**
     * Find projects for current user
     *
     * @return void
     */
    protected function findByCurrentUser(
        $user,
        ProjectFilterInterface $projectFilter
    ){
        return $this->fetchProjects(
            '/api/v1/user/repos',
            $projectFilter
        );
    }

    /**
     * Find projects by username
     *
     * @return void
     */
    protected function findByUser(
        $user,
        ProjectFilterInterface $projectFilter
    ){
        return $this->fetchProjects(
            '/api/v1/users/'.$user.'/repos',
            $projectFilter
        );
    }

    /**
     * Find projects by username
     *
     * @return void
     */
    protected function findByOrg(
        $org,
        ProjectFilterInterface $projectFilter
    ){
        return $this->fetchProjects(
            '/api/v1/orgs/'.$org.'/repos',
            $projectFilter
        );
    }

    /**
     * Fetch all pages for a given URI
     *
     * @param string $path such as '/orgs/IGNF/repos' or '/users/mborne/repos'
     * @return ProjectInterface[]
     */
    private function fetchProjects(
        $path,
        ProjectFilterInterface $projectFilter
    ){
        $result = array();

        $uri = $path.'?limit='.self::DEFAULT_PER_PAGE;
        $this->logger->debug('GET '.$uri);
        $response = $this->httpClient->get($uri);
        $rawProjects = json_decode( (string)$response->getBody(), true ) ;
        foreach ( $rawProjects as $rawProject ){
            $project = new GogsProject($rawProject);
            if ( ! $projectFilter->isAccepted($project) ){
                continue;
            }
            $result[] = $project;
        }

        return $result;
    }

    /*
     * @{inheritDoc}
     */
    public function getRawFile(
        ProjectInterface $project, 
        $filePath,
        $ref
    ){
        $uri  = '/api/v1/repos/'.$project->getName().'/raw/';
        $uri .= $project->getDefaultBranch();
        $uri .= '/'.$filePath;

        $this->logger->debug('GET '.$uri);
        $response = $this->httpClient->get($uri);
        return (string)$response->getBody();
    }


}