<?php

namespace MBO\RemoteGit\Tests;

use Psr\Log\NullLogger;
use MBO\RemoteGit\ClientOptions;
use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Github\GithubClient;
use MBO\RemoteGit\Github\GithubProject;

class GithubClientTest extends TestCase
{
    /**
     * @return GithubClient
     */
    protected function createGithubClient()
    {
        $token = getenv('SATIS_GITHUB_TOKEN');
        if (empty($token)) {
            $this->markTestSkipped('Missing SATIS_GITHUB_TOKEN for github.com');
        }

        $clientOptions = new ClientOptions();
        $clientOptions
            ->setUrl('https://github.com')
            ->setToken($token)
        ;

        /* create client */
        return ClientFactory::createClient(
            $clientOptions,
            new NullLogger()
        );
    }

    /**
     * Ensure client can find mborne's projects
     */
    public function testUserAndOrgsRepositories()
    {
        /* create client */
        $client = $this->createGithubClient();
        $this->assertInstanceOf(GithubClient::class, $client);

        /* search projects */
        $options = new FindOptions();
        $options->setUsers(['mborne']);
        $options->setOrganizations(['IGNF']);
        $projects = $client->find($options);
        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GithubProject::class, $project);
            $this->assertGettersWorks($project);
            $projectsByName[$project->getName()] = $project;
        }

        $this->assertArrayHasKey(
            'IGNF/validator',
            $projectsByName
        );

        /* check project found */
        $this->assertArrayHasKey(
            'mborne/satis-gitlab',
            $projectsByName
        );

        $project = $projectsByName['mborne/satis-gitlab'];
        $composer = $client->getRawFile(
            $project,
            'composer.json',
            $project->getDefaultBranch()
        );
        $this->assertContains('mborne@users.noreply.github.com', $composer);

        $testFileInSubdirectory = $client->getRawFile(
            $project,
            'tests/TestCase.php',
            $project->getDefaultBranch()
        );
        $this->assertContains('class TestCase', $testFileInSubdirectory);
    }

    /**
     * Ensure client can find mborne's projects with composer.json file
     */
    public function testFilterFile()
    {
        /* create client */
        $client = $this->createGithubClient();
        $this->assertInstanceOf(GithubClient::class, $client);

        /* search projects */
        $options = new FindOptions();
        $options->setUsers(['mborne']);
        $projects = $client->find($options);
        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GithubProject::class, $project);
            $projectsByName[$project->getName()] = $project;
        }

        /* check project found */
        $this->assertArrayHasKey(
            'mborne/satis-gitlab',
            $projectsByName
        );

        $project = $projectsByName['mborne/satis-gitlab'];
        $composer = $client->getRawFile(
            $project,
            'composer.json',
            $project->getDefaultBranch()
        );
        $this->assertContains('mborne@users.noreply.github.com', $composer);

        $testFileInSubdirectory = $client->getRawFile(
            $project,
            'tests/TestCase.php',
            $project->getDefaultBranch()
        );
        $this->assertContains('class TestCase', $testFileInSubdirectory);
    }
}
