<?php

namespace MBO\RemoteGit\Gogs;

use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\AbstractClient;
use MBO\RemoteGit\Exception\RawFileNotFoundException;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Http\TokenType;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ProjectInterface;
use Psr\Log\LoggerInterface;

/**
 * Client implementation for gogs and gitea.
 *
 * @author mborne
 */
class GogsClient extends AbstractClient
{
    public const TYPE = 'gogs-v1';
    public const TOKEN_TYPE = TokenType::AUTHORIZATION_TOKEN;
    private const LIMIT_PER_PAGE_PARAM = 'limit';

    public function __construct(
        GuzzleHttpClient $httpClient,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($httpClient, self::LIMIT_PER_PAGE_PARAM, $logger);
    }

    protected function createProject(array $rawProject): GogsProject
    {
        return new GogsProject($rawProject);
    }

    public function getProjects(FindOptions $options): iterable
    {
        if (empty($options->getUsers()) && empty($options->getOrganizations())) {
            yield from $this->findByCurrentUser(
                $options->getFilter()
            );
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
     * Find projects for current user.
     *
     * @return iterable<ProjectInterface>
     */
    protected function findByCurrentUser(
        ProjectFilterInterface $projectFilter,
    ): iterable {
        yield from $this->fetchAllPages(
            '/api/v1/user/repos',
            $projectFilter
        );
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
            '/api/v1/users/'.$user.'/repos',
            $projectFilter
        );
    }

    /**
     * Find projects by organization.
     *
     * @return iterable<ProjectInterface>
     */
    protected function findByOrg(
        string $org,
        ProjectFilterInterface $projectFilter,
    ): iterable {
        yield from $this->fetchAllPages(
            '/api/v1/orgs/'.$org.'/repos',
            $projectFilter
        );
    }

    public function getRawFile(
        ProjectInterface $project,
        $filePath,
        $ref,
    ): string {
        $uri = '/api/v1/repos/'.$project->getName().'/raw/';
        $uri .= $project->getDefaultBranch();
        $uri .= '/'.$filePath;

        try {
            $this->getLogger()->debug('GET '.$uri);
            $response = $this->getHttpClient()->request('GET', $uri);

            return (string) $response->getBody();
        } catch (\Exception $e) {
            throw new RawFileNotFoundException($filePath, $ref, $e);
        }
    }
}
