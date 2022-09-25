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
     * @return GitlabClient
     */
    protected function createGitlabClient()
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
        return ClientFactory::createClient(
            $clientOptions,
            new NullLogger()
        );
    }

    /**
     * Ensure client can find mborne/sample-composer by username
     */
    public function testGitlabDotComByUser()
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
        $composer = $client->getRawFile(
            $project,
            'composer.json',
            $project->getDefaultBranch()
        );
        $this->assertStringContainsString('mborne@users.noreply.github.com', $composer);
    }

    public function testGitlabDotComOrgs()
    {
        /* create client */
        $client = $this->createGitlabClient();
        $this->assertInstanceOf(GitlabClient::class, $client);

        /* search projects */
        $findOptions = new FindOptions();
        $findOptions->setOrganizations(['gitlab-org']);
        $projects = $client->find($findOptions);
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
    public function testGitlabDotComSearch()
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

        $project = $projectsByName['mborne/sample-composer'];
        $composer = $client->getRawFile(
            $project,
            'composer.json',
            $project->getDefaultBranch()
        );
        $this->assertStringContainsString('mborne@users.noreply.github.com', $composer);
    }
}
