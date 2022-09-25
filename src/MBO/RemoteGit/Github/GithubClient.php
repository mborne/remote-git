<?php

namespace MBO\RemoteGit\Github;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use MBO\RemoteGit\AbstractClient;
use MBO\RemoteGit\Exception\RequiredParameterException;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Helper\LoggerHelper;
use MBO\RemoteGit\Http\TokenType;

/**
 * Client implementation for github
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
     * @var GuzzleHttpClient
     */
    protected $httpClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor with an http client and a logger
     *
     * @param $httpClient http client
     * @param $logger
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(
        GuzzleHttpClient $httpClient,
        LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->logger = LoggerHelper::handleNull($logger);
    }

    /**
     * {@inheritdoc}
     */
    protected function createProject(array $rawProject)
    {
        return new GithubProject($rawProject);
    }

    /**
     * {@inheritdoc}
     */
    public function find(FindOptions $options)
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
     * Find projects by username
     *
     * @return ProjectInterface[]
     */
    protected function findByUser(
        $user,
        ProjectFilterInterface $projectFilter
    ) {
        return $this->fetchAllPages(
            ('_me_' == $user) ? '/user/repos' : '/users/'.$user.'/repos',
            $projectFilter
        );
    }

    /**
     * Find projects by username
     *
     * @return ProjectInterface[]
     */
    protected function findByOrg(
        $org,
        ProjectFilterInterface $projectFilter
    ) {
        return $this->fetchAllPages(
            '/orgs/'.$org.'/repos',
            $projectFilter
        );
    }

    /**
     * Fetch all pages for a given URI
     *
     * @param string $path such as '/orgs/IGNF/repos' or '/users/mborne/repos'
     *
     * @return ProjectInterface[]
     */
    private function fetchAllPages(
        $path,
        ProjectFilterInterface $projectFilter
    ) {
        $result = [];
        for ($page = 1; $page <= self::MAX_PAGES; ++$page) {
            $params = [
                'page' => $page,
                'per_page' => self::DEFAULT_PER_PAGE,
            ];
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
        $metadata = $project->getRawMetadata();
        $uri = str_replace(
            '{+path}',
            urlencode($filePath),
            $metadata['contents_url']
        );
        $uri .= '?ref='.$ref;
        $this->getLogger()->debug('GET '.$uri);
        $response = $this->getHttpClient()->request('GET', $uri, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3.raw',
            ],
        ]);

        return (string) $response->getBody();
    }
}
