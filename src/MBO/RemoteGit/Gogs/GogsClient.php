<?php

namespace MBO\RemoteGit\Gogs;

use Psr\Log\LoggerInterface;
use \GuzzleHttp\Client as GuzzleHttpClient;

use MBO\RemoteGit\AbstractClient;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Helper\LoggerHelper;
use MBO\RemoteGit\Http\TokenType;

/**
 * 
 * Client implementation for gogs
 * 
 * @author mborne
 * 
 */
class GogsClient extends AbstractClient
{

    const TYPE = 'gogs';
    const TOKEN_TYPE = TokenType::AUTHORIZATION_TOKEN;

    const DEFAULT_PER_PAGE = 1000;

    /*
     * @{inheritDoc}
     */
    public function __construct(
        GuzzleHttpClient $httpClient,
        LoggerInterface $logger = null
    ) {
        parent::__construct($httpClient, $logger);
    }
    
    /*
     * @{inheritDoc}
     */
    protected function createProject(array $rawProject)
    {
        return new GogsProject($rawProject);
    }

    /*
     * @{inheritDoc}
     */
    public function find(FindOptions $options)
    {
        if (empty($options->getUsers()) && empty($options->getOrganizations())) {
            return $this->findByCurrentUser(
                $options->getFilter()
            );
        }

        $result = array();
        foreach ($options->getUsers() as $user) {
            $result = array_merge($result, $this->findByUser(
                $user,
                $options->getFilter()
            ));
        }
        foreach ($options->getOrganizations() as $org) {
            $result = array_merge($result, $this->findByOrg(
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
        ProjectFilterInterface $projectFilter
    ) {
        return $this->filter(
            $this->getProjects(
                '/api/v1/user/repos',
                [
                    'limit' => self::DEFAULT_PER_PAGE
                ]
            ),
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
    ) {
        return $this->filter(
            $this->getProjects(
                '/api/v1/users/' . $user . '/repos',
                [
                    'limit' => self::DEFAULT_PER_PAGE
                ]
            ),
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
    ) {
        return $this->filter(
            $this->getProjects(
                '/api/v1/orgs/' . $org . '/repos',
                [
                    'limit' => self::DEFAULT_PER_PAGE
                ]
            ),
            $projectFilter
        );
    }


    /*
     * @{inheritDoc}
     */
    public function getRawFile(
        ProjectInterface $project,
        $filePath,
        $ref
    ) {
        $uri = '/api/v1/repos/' . $project->getName() . '/raw/';
        $uri .= $project->getDefaultBranch();
        $uri .= '/' . $filePath;

        $this->getLogger()->debug('GET ' . $uri);
        $response = $this->getHttpClient()->request('GET',$uri);
        return (string)$response->getBody();
    }


}