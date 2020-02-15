<?php

namespace MBO\RemoteGit;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\Helper\LoggerHelper;

/**
 * Abstract class providing a framework to implement clients
 * based on API
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var GuzzleHttpClient
     */
    protected $httpClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor with an httpClient ready to performs API requests
     *
     * @param LoggerInterface $logger
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function __construct(
        GuzzleHttpClient $httpClient,
        LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->logger = LoggerHelper::handleNull($logger);
    }

    /**
     * @return GuzzleHttpClient
     */
    protected function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * Create a project according to JSON metadata provided by an API
     *
     * @return ProjectInterface
     */
    abstract protected function createProject(array $rawProject);

    /**
     * Get projets for a given path with parameters
     *
     * @return ProjectInterface[]
     */
    protected function getProjects(
        $path,
        array $params = []
    ) {
        $uri = $path.'?'.$this->implodeParams($params);
        $this->getLogger()->debug('GET '.$uri);
        $response = $this->getHttpClient()->request('GET', $uri);
        $rawProjects = json_decode((string) $response->getBody(), true);
        $projects = [];
        foreach ($rawProjects as $rawProject) {
            $projects[] = $this->createProject($rawProject);
        }

        return $projects;
    }

    /**
     * Implode params to performs request
     *
     * @param array $params key=>value
     *
     * @return string
     */
    protected function implodeParams($params)
    {
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key.'='.urlencode($value);
        }

        return implode('&', $parts);
    }

    /**
     * Helper to apply filter to a project list
     *
     * @param ProjectInterface[] $projects
     *
     * @return ProjectInterface[]
     */
    protected function filter(array $projects, ProjectFilterInterface $filter)
    {
        $result = [];
        foreach ($projects as $project) {
            if (!$filter->isAccepted($project)) {
                continue;
            }
            $result[] = $project;
        }

        return $result;
    }
}
