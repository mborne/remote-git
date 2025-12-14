<?php

namespace MBO\RemoteGit;

use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\Helper\LoggerHelper;
use Psr\Log\LoggerInterface;

/**
 * Abstract class providing a framework to implement clients
 * based on API.
 */
abstract class AbstractClient implements ClientInterface
{
    private const DEFAULT_PER_PAGE = 50;
    private const MAX_PAGES = 1000;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor with an httpClient ready to performs API requests.
     *
     * @param string $limitPerPageParam 'per_page' (GitLab, GitHub) or 'limit' (Gogs, Gitea)
     */
    protected function __construct(
        protected GuzzleHttpClient $httpClient,
        private string $limitPerPageParam,
        ?LoggerInterface $logger = null,
    ) {
        $this->httpClient = $httpClient;
        $this->logger = LoggerHelper::handleNull($logger);
    }

    protected function getHttpClient(): GuzzleHttpClient
    {
        return $this->httpClient;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Create a project according to JSON metadata provided by an API.
     *
     * @param array<string,mixed> $rawProject
     */
    abstract protected function createProject(array $rawProject): ProjectInterface;

    public function find(FindOptions $options): array
    {
        return iterator_to_array($this->getProjects($options), false);
    }

    /**
     * Get projets for a given path with parameters.
     *
     * @param array<string,string|int> $params
     *
     * @return ProjectInterface[]
     */
    private function fetchProjects(
        string $path,
        array $params = [],
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
     * Fetch all pages for a given path with query params.
     *
     * @param string                 $path          ex : "/api/v4/projects"
     * @param ProjectFilterInterface $projectFilter client side filtering
     * @param array<string,string>   $extraParams   ex : ['affiliation' => 'owner']
     *
     * @return iterable<ProjectInterface>
     */
    protected function fetchAllPages(
        string $path,
        ProjectFilterInterface $projectFilter,
        array $extraParams = [],
    ): iterable {
        for ($page = 1; $page <= self::MAX_PAGES; ++$page) {
            $params = array_merge($extraParams, [
                'page' => $page,
                $this->limitPerPageParam => self::DEFAULT_PER_PAGE,
            ]);
            $projects = $this->fetchProjects($path, $params);
            if (empty($projects)) {
                break;
            }
            foreach ($projects as $project) {
                if ($projectFilter->isAccepted($project)) {
                    yield $project;
                }
            }
        }
    }

    /**
     * Implode params to performs HTTP request.
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
}
