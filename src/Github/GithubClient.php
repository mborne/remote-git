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
    private const LIMIT_PER_PAGE_PARAM = 'per_page';

    /**
     * Constructor with an http client and a logger.
     *
     * @param $httpClient http client
     */
    public function __construct(
        GuzzleHttpClient $httpClient,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($httpClient, self::LIMIT_PER_PAGE_PARAM, $logger);
    }

    protected function createProject(array $rawProject): GithubProject
    {
        return new GithubProject($rawProject);
    }

    public function getProjects(FindOptions $options): iterable
    {
        if (empty($options->getUsers()) && empty($options->getOrganizations())) {
            throw new RequiredParameterException('[GithubClient]Define at least an org or an user');
        }
        foreach ($options->getUsers() as $user) {
            yield from $this->findByUser(
                $user,
                $options->getFilter()
            );
        }
        foreach ($options->getOrganizations() as $org) {
            yield from $this->findByOrg(
                $org,
                $options->getFilter()
            );
        }
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
    ): iterable {
        /*
         * Use /user/repos?affiliation=owner for special user _me_
         */
        if ('_me_' == $user) {
            yield from $this->fetchAllPages(
                '/user/repos',
                $projectFilter,
                extraParams: [
                    'affiliation' => 'owner',
                ]
            );
        } else {
            yield from $this->fetchAllPages(
                '/users/'.$user.'/repos',
                $projectFilter
            );
        }
    }

    /**
     * Find projects by org name.
     *
     * @return iterable<ProjectInterface>
     */
    protected function findByOrg(
        string $org,
        ProjectFilterInterface $projectFilter,
    ): iterable {
        yield from $this->fetchAllPages(
            '/orgs/'.$org.'/repos',
            $projectFilter
        );
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
