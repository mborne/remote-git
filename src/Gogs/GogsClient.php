<?php

namespace MBO\RemoteGit\Gogs;

use Exception;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\AbstractClient;
use MBO\RemoteGit\Exception\RawFileNotFoundException;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Http\TokenType;

/**
 * Client implementation for gogs and gitea.
 *
 * @author mborne
 */
class GogsClient extends AbstractClient
{
    public const TYPE = 'gogs-v1';
    public const TOKEN_TYPE = TokenType::AUTHORIZATION_TOKEN;

    public const DEFAULT_PER_PAGE = 1000;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        GuzzleHttpClient $httpClient,
        LoggerInterface $logger = null
    ) {
        parent::__construct($httpClient, $logger);
    }

    /**
     * {@inheritDoc}
     */
    protected function createProject(array $rawProject): GogsProject
    {
        return new GogsProject($rawProject);
    }

    /**
     * {@inheritDoc}
     */
    public function find(FindOptions $options): array
    {
        if (empty($options->getUsers()) && empty($options->getOrganizations())) {
            return $this->findByCurrentUser(
                $options->getFilter()
            );
        }

        $result = [];
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
     * @return ProjectInterface[]
     */
    protected function findByCurrentUser(
        ProjectFilterInterface $projectFilter
    ): array {
        return $this->filter(
            $this->getProjects(
                '/api/v1/user/repos',
                [
                    'limit' => self::DEFAULT_PER_PAGE,
                ]
            ),
            $projectFilter
        );
    }

    /**
     * Find projects by username
     *
     * @return ProjectInterface[]
     */
    protected function findByUser(
        string $user,
        ProjectFilterInterface $projectFilter
    ): array {
        return $this->filter(
            $this->getProjects(
                '/api/v1/users/'.$user.'/repos',
                [
                    'limit' => self::DEFAULT_PER_PAGE,
                ]
            ),
            $projectFilter
        );
    }

    /**
     * Find projects by organization
     *
     * @return ProjectInterface[]
     */
    protected function findByOrg(
        string $org,
        ProjectFilterInterface $projectFilter
    ): array {
        return $this->filter(
            $this->getProjects(
                '/api/v1/orgs/'.$org.'/repos',
                [
                    'limit' => self::DEFAULT_PER_PAGE,
                ]
            ),
            $projectFilter
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRawFile(
        ProjectInterface $project,
        $filePath,
        $ref
    ): string {
        $uri = '/api/v1/repos/'.$project->getName().'/raw/';
        $uri .= $project->getDefaultBranch();
        $uri .= '/'.$filePath;

        try {
            $this->getLogger()->debug('GET '.$uri);
            $response = $this->getHttpClient()->request('GET', $uri);

            return (string) $response->getBody();
        } catch (Exception $e) {
            throw new RawFileNotFoundException($filePath, $ref, $e);
        }
    }
}
