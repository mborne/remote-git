<?php

namespace MBO\RemoteGit\Tests;

use Psr\Log\NullLogger;
use MBO\RemoteGit\ClientOptions;
use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Gitlab\GitlabClient;
use MBO\RemoteGit\Gitlab\GitlabProject;

class GitlabClientTest extends TestCase
{
    /**
     * Create GitlabClient using GITLAB_TOKEN.
     */
    protected function createGitlabClient(): GitlabClient
    {
        $gitlabToken = getenv('GITLAB_TOKEN');
        if (empty($gitlabToken)) {
            $this->markTestSkipped('Missing GITLAB_TOKEN for gitlab.com');
        }

        $clientOptions = new ClientOptions();
        $clientOptions
            ->setUrl('https://gitlab.com')
            ->setToken($gitlabToken)
        ;

        /* create client */
        $client = ClientFactory::createClient(
            $clientOptions,
            new NullLogger()
        );
        $this->assertInstanceOf(GitlabClient::class, $client);

        return $client;
    }

    /**
     * Ensure client can find mborne/sample-composer by username
     */
    public function testGitlabDotComByUser(): void
    {
        /* create client */
        $client = $this->createGitlabClient();
        $this->assertInstanceOf(GitlabClient::class, $client);

        /* search projects */
        $findOptions = new FindOptions();
        $findOptions->setUsers(['mborne']);
        $projects = $client->find($findOptions);
        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GitlabProject::class, $project);
            $this->assertGettersWorks($project);
            $projectsByName[$project->getName()] = $project;
        }
        /* check project found */
        $this->assertArrayHasKey(
            'mborne/sample-composer',
            $projectsByName
        );

        $project = $projectsByName['mborne/sample-composer'];
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);
        $composer = $client->getRawFile(
            $project,
            'composer.json',
            $defaultBranch
        );
        $this->assertStringContainsString('mborne@users.noreply.github.com', $composer);
    }

    public function testGitlabDotComOrgs(): void
    {
        /* create client */
        $client = $this->createGitlabClient();
        $this->assertInstanceOf(GitlabClient::class, $client);

        /* search projects */
        $findOptions = new FindOptions();
        $findOptions->setOrganizations(['gitlab-org']);
        $projects = $client->find($findOptions);
        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GitlabProject::class, $project);
            $this->assertGettersWorks($project);
            $projectsByName[$project->getName()] = $project;
        }
        $this->assertArrayHasKey(
            'gitlab-org/gitlab-runner',
            $projectsByName
        );
    }

    /**
     * Ensure client can find mborne/sample-composer with search
     */
    public function testGitlabDotComSearch(): void
    {
        /* create client */
        $client = $this->createGitlabClient();
        $this->assertInstanceOf(GitlabClient::class, $client);

        /* search projects */
        $findOptions = new FindOptions();
        $findOptions->setSearch('sample-composer');
        $projects = $client->find($findOptions);
        $projectsByName = [];
        foreach ($projects as $project) {
            $projectsByName[$project->getName()] = $project;
        }
        /* check project found */
        $this->assertArrayHasKey(
            'mborne/sample-composer',
            $projectsByName
        );

        /* test getRawFile */
        $project = $projectsByName['mborne/sample-composer'];
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);
        $composer = $client->getRawFile(
            $project,
            'composer.json',
            $defaultBranch
        );
        $this->assertStringContainsString('mborne@users.noreply.github.com', $composer);

        /* test getRawFile not found */
        $this->ensureThatRawFileNotFoundThrowsException($client, $project);
    }
}
