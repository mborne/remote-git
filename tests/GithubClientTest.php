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
     * Create GithubClient using GITHUB_TOKEN.
     */
    protected function createGithubClient(): GithubClient
    {
        $token = getenv('GITHUB_TOKEN');
        if (empty($token)) {
            $this->markTestSkipped('Missing GITHUB_TOKEN for github.com');
        }

        $clientOptions = new ClientOptions();
        $clientOptions
            ->setUrl('https://github.com')
            ->setToken($token)
        ;

        /* create client */
        $client = ClientFactory::createClient(
            $clientOptions,
            new NullLogger()
        );
        $this->assertInstanceOf(GithubClient::class, $client);

        return $client;
    }

    /**
     * Ensure client can find mborne's projects
     */
    public function testUserAndOrgsRepositories(): void
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

        /* check project found */
        $this->assertArrayHasKey(
            'IGNF/validator',
            $projectsByName
        );
        $this->assertArrayHasKey(
            'mborne/satis-gitlab',
            $projectsByName
        );

        $project = $projectsByName['mborne/satis-gitlab'];
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);
        $composer = $client->getRawFile(
            $project,
            'composer.json',
            $defaultBranch
        );
        $this->assertStringContainsString('mborne@users.noreply.github.com', $composer);

        /* test getRawFile */
        $testFileInSubdirectory = $client->getRawFile(
            $project,
            'tests/TestCase.php',
            $defaultBranch
        );
        $this->assertStringContainsString('class TestCase', $testFileInSubdirectory);

        /* test getRawFile not found */
        $this->ensureThatRawFileNotFoundThrowsException($client, $project);
    }

    /**
     * Ensure client can find mborne's projects with composer.json file
     */
    public function testFilterFile(): void
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
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);
        $composer = $client->getRawFile(
            $project,
            'composer.json',
            $defaultBranch
        );
        $this->assertStringContainsString('mborne@users.noreply.github.com', $composer);

        $testFileInSubdirectory = $client->getRawFile(
            $project,
            'tests/TestCase.php',
            $defaultBranch
        );
        $this->assertStringContainsString('class TestCase', $testFileInSubdirectory);
    }

    /**
     * Ensure client can find mborne's projects using _me_
     */
    public function testFakeUserMe(): void
    {
        $ci = getenv('CI');
        if (!empty($ci)) {
            $this->markTestSkipped('https://api.github.com/user/repos usage is forbidden in actions ("Resource not accessible by integration")');
        }

        $client = $this->createGithubClient();
        $this->assertInstanceOf(GithubClient::class, $client);

        /* search projects */
        $options = new FindOptions();
        $options->setUsers(['_me_']);
        $projects = $client->find($options);
        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GithubProject::class, $project);
            $this->assertGettersWorks($project);
            $this->assertStringStartsWith('mborne/', $project->getName());
            $projectsByName[$project->getName()] = $project;
        }

        // check public project
        $this->assertArrayHasKey(
            'mborne/remote-git',
            $projectsByName
        );
        // check private project
        $this->assertArrayHasKey(
            'mborne/tp-pattern-geometry-correction',
            $projectsByName
        );
    }
}
