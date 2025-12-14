<?php

namespace MBO\RemoteGit\Gitlab;

use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\AbstractClient;
use MBO\RemoteGit\Exception\RawFileNotFoundException;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Http\TokenType;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ProjectInterface;
use Psr\Log\LoggerInterface;

/**
 * Find gitlab projects.
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
    private const LIMIT_PER_PAGE_PARAM = 'per_page';

    /**
     * Constructor with an http client and a logger.
     */
    public function __construct(
        GuzzleHttpClient $httpClient,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($httpClient, self::LIMIT_PER_PAGE_PARAM, $logger);
    }

    protected function createProject(array $rawProject): GitlabProject
    {
        return new GitlabProject($rawProject);
    }

    public function getProjects(FindOptions $options): iterable
    {
        /* find all projects applying optional search */
        if (empty($options->getUsers()) && empty($options->getOrganizations())) {
            yield from $this->findBySearch($options);
        }

        foreach ($options->getUsers() as $user) {
            yield from $this->findByUser(
                $user,
                $options->getFilter()
            );
        }
        foreach ($options->getOrganizations() as $org) {
            yield from $this->findByGroup(
                $org,
                $options->getFilter()
            );
        }
    }

    /**
     * Find projects by username.
     *
     * @return iterable<ProjectInterface>
     */
    protected function findByUser(
        string $user,
        ProjectFilterInterface $projectFilter,
    ): iterable {
        yield from $this->fetchAllPages(
            '/api/v4/users/'.urlencode($user).'/projects',
            $projectFilter
        );
    }

    /**
     * Find projects by group.
     *
     * @return iterable<ProjectInterface>
     */
    protected function findByGroup(
        string $group,
        ProjectFilterInterface $projectFilter,
    ): iterable {
        yield from $this->fetchAllPages(
            '/api/v4/groups/'.urlencode($group).'/projects',
            $projectFilter
        );
    }

    /**
     * Find all projects using option search.
     *
     * @return iterable<ProjectInterface>
     */
    protected function findBySearch(FindOptions $options): iterable
    {
        $path = '/api/v4/projects';
        $params = [];
        if ($options->hasSearch()) {
            $params['search'] = $options->getSearch();
        }

        yield from $this->fetchAllPages(
            $path,
            $options->getFilter(),
            $params
        );
    }

    public function getRawFile(
        ProjectInterface $project,
        string $filePath,
        string $ref,
    ): string {
        // ref : https://docs.gitlab.com/ee/api/repository_files.html#get-raw-file-from-repository
        $uri = '/api/v4/projects/'.$project->getId().'/repository/files/'.urlencode($filePath).'/raw';
        $uri .= '?ref='.$ref;
        try {
            $this->getLogger()->debug('GET '.$uri);
            $response = $this->httpClient->request('GET', $uri);

            return (string) $response->getBody();
        } catch (\Exception $e) {
            throw new RawFileNotFoundException($filePath, $ref);
        }
    }
}
