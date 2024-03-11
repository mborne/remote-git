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
    protected function getHttpClient(): GuzzleHttpClient
    {
        return $this->httpClient;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Create a project according to JSON metadata provided by an API
     *
     * @param array<string,mixed> $rawProject
     *
     * @return ProjectInterface
     */
    abstract protected function createProject(array $rawProject): ProjectInterface;

    /**
     * Get projets for a given path with parameters
     *
     * @param array<string,string|int> $params
     *
     * @return ProjectInterface[]
     */
    protected function getProjects(
        string $path,
        array $params = []
    ): array {
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
     * Implode params to performs HTTP request
     *
     * @param array<string,string|int> $params key=>value
     */
    protected function implodeParams(array $params): string
    {
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key.'='.urlencode((string) $value);
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
    protected function filter(array $projects, ProjectFilterInterface $filter): array
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
