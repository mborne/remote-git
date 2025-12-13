<?php

namespace MBO\RemoteGit\Github;

use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\AbstractClient;
use MBO\RemoteGit\Exception\RawFileNotFoundException;
use MBO\RemoteGit\Exception\RequiredParameterException;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Http\TokenType;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ProjectInterface;
use Psr\Log\LoggerInterface;

/**
 * Client implementation for github.
 *
 * See following github docs :
 *
 * https://developer.github.com/v3/repos/#list-organization-repositories
 * https://developer.github.com/v3/repos/#list-user-repositories
 * https://developer.github.com/v3/#pagination
 *
 * @author mborne
 */
class GithubClient extends AbstractClient
{
    public const TYPE = 'github';
    public const TOKEN_TYPE = TokenType::AUTHORIZATION_TOKEN;

    public const DEFAULT_PER_PAGE = 100;
    public const MAX_PAGES = 10000;

    /**
     * Constructor with an http client and a logger.
     *
     * @param $httpClient http client
     */
    public function __construct(
        GuzzleHttpClient $httpClient,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($httpClient, $logger);
    }

    protected function createProject(array $rawProject): GithubProject
    {
        return new GithubProject($rawProject);
    }

    public function find(FindOptions $options): array
    {
        $result = [];
        if (empty($options->getUsers()) && empty($options->getOrganizations())) {
            throw new RequiredParameterException('[GithubClient]Define at least an org or a user to use find');
        }
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
     * Find projects by username.
     *
     * @see https://docs.github.com/en/rest/repos/repos#list-repositories-for-the-authenticated-user
     * @see https://docs.github.com/en/rest/repos/repos#list-repositories-for-a-user
     *
     * @return ProjectInterface[]
     */
    protected function findByUser(
        string $user,
        ProjectFilterInterface $projectFilter,
    ) {
        /*
         * Use /user/repos?affiliation=owner for special user _me_
         */
        if ('_me_' == $user) {
            return $this->fetchAllPages(
                '/user/repos',
                $projectFilter,
                [
                    'affiliation' => 'owner',
                ]
            );
        }

        return $this->fetchAllPages(
            '/users/'.$user.'/repos',
            $projectFilter
        );
    }

    /**
     * Find projects by username.
     *
     * @return ProjectInterface[]
     */
    protected function findByOrg(
        string $org,
        ProjectFilterInterface $projectFilter,
    ) {
        return $this->fetchAllPages(
            '/orgs/'.$org.'/repos',
            $projectFilter
        );
    }

    /**
     * Fetch all pages for a given URI.
     *
     * @param string                   $path        such as '/orgs/IGNF/repos' or '/users/mborne/repos'
     * @param array<string,string|int> $extraParams
     *
     * @return ProjectInterface[]
     */
    private function fetchAllPages(
        string $path,
        ProjectFilterInterface $projectFilter,
        array $extraParams = [],
    ): array {
        $result = [];
        for ($page = 1; $page <= self::MAX_PAGES; ++$page) {
            $params = array_merge($extraParams, [
                'page' => $page,
                'per_page' => self::DEFAULT_PER_PAGE,
            ]);
            $projects = $this->fetchProjects($path, $params);
            if (empty($projects)) {
                break;
            }
            $result = array_merge($result, $this->filter($projects, $projectFilter));
        }

        return $result;
    }

    public function getRawFile(
        ProjectInterface $project,
        $filePath,
        $ref,
    ): string {
        $metadata = $project->getRawMetadata();
        $uri = str_replace(
            '{+path}',
            urlencode($filePath),
            $metadata['contents_url']
        );
        $uri .= '?ref='.$ref;

        try {
            $this->getLogger()->debug('GET '.$uri);
            $response = $this->getHttpClient()->request('GET', $uri, [
                'headers' => [
                    'Accept' => 'application/vnd.github.v3.raw',
                ],
            ]);

            return (string) $response->getBody();
        } catch (\Exception $e) {
            throw new RawFileNotFoundException($filePath, $ref, $e);
        }
    }
}
