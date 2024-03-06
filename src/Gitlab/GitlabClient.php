<?php

namespace MBO\RemoteGit\Gitlab;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\AbstractClient;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Http\TokenType;

/**
 * Find gitlab projects
 *
 * See following gitlab docs :
 *
 * https://docs.gitlab.com/ee/api/projects.html#list-all-projects
 * https://docs.gitlab.com/ee/api/projects.html#search-for-projects-by-name
 *
 * @author mborne
 */
class GitlabClient extends AbstractClient
{
    public const TYPE = 'gitlab-v4';
    public const TOKEN_TYPE = TokenType::PRIVATE_TOKEN;

    public const DEFAULT_PER_PAGE = 50;
    public const MAX_PAGES = 10000;

    /**
     * Constructor with an http client and a logger
     *
     * @param $httpClient http client
     * @param $logger
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
        return new GitlabProject($rawProject);
    }

    /*
     * @{inheritDoc}
     */
    public function find(FindOptions $options)
    {
        /* find all projects applying optional search */
        if (empty($options->getUsers()) && empty($options->getOrganizations())) {
            return $this->findBySearch($options);
        }

        $result = [];
        foreach ($options->getUsers() as $user) {
            $result = array_merge($result, $this->findByUser(
                $user,
                $options->getFilter()
            ));
        }
        foreach ($options->getOrganizations() as $org) {
            $result = array_merge($result, $this->findByGroup(
                $org,
                $options->getFilter()
            ));
        }

        return $result;
    }

    /**
     * Find projects by username
     *
     * @return ProjectInterface[]
     */
    protected function findByUser(
        $user,
        ProjectFilterInterface $projectFilter
    ) {
        return $this->fetchAllPages(
            '/api/v4/users/'.urlencode($user).'/projects',
            [],
            $projectFilter
        );
    }

    /**
     * Find projects by group
     *
     * @return ProjectInterface[]
     */
    protected function findByGroup(
        $group,
        ProjectFilterInterface $projectFilter
    ) {
        return $this->fetchAllPages(
            '/api/v4/groups/'.urlencode($group).'/projects',
            [],
            $projectFilter
        );
    }

    /**
     * Find all projects using option search
     *
     * @return ProjectInterface[]
     */
    protected function findBySearch(FindOptions $options)
    {
        $path = '/api/v4/projects';
        $params = [];
        if ($options->hasSearch()) {
            $params['search'] = $options->getSearch();
        }

        return $this->fetchAllPages(
            $path,
            $params,
            $options->getFilter()
        );
    }

    /**
     * Fetch all pages for a given path with query params
     *
     * @param string $path   ex : "/api/v4/projects"
     * @param array  $params ex : array('search'=>'sample-composer')
     *
     * @return ProjectInterface[]
     */
    private function fetchAllPages(
        $path,
        array $params,
        ProjectFilterInterface $projectFilter
    ) {
        $result = [];

        for ($page = 1; $page <= self::MAX_PAGES; ++$page) {
            $params['page'] = $page;
            $params['per_page'] = self::DEFAULT_PER_PAGE;

            $projects = $this->getProjects($path, $params);
            if (empty($projects)) {
                break;
            }
            $result = array_merge($result, $this->filter($projects, $projectFilter));
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
    ) {
        // ref : https://docs.gitlab.com/ee/api/repository_files.html#get-raw-file-from-repository
        $uri = '/api/v4/projects/'.$project->getId().'/repository/files/'.urlencode($filePath).'/raw';
        $uri .= '?ref='.$ref;
        $this->getLogger()->debug('GET '.$uri);
        $response = $this->httpClient->request('GET', $uri);

        return (string) $response->getBody();
    }
}
