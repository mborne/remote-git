<?php

namespace MBO\RemoteGit\Tests\Gitlab;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use MBO\RemoteGit\Gitlab\GitlabClient;
use MBO\RemoteGit\Gitlab\GitlabProject;
use MBO\RemoteGit\Tests\TestCase;

/**
 * Test GitlabClient without API call, gitlab providing "empty_repo"
 * in the project metadata.
 */
class GitlabClientTest extends TestCase
{
    /**
     * Create a client which fails if an API call is performed.
     */
    private function createGitlabClient(): GitlabClient
    {
        return new GitlabClient(new GuzzleHttpClient([
            'base_uri' => 'https://gitlab.com',
            'handler' => HandlerStack::create(new MockHandler()),
        ]));
    }

    public function testIsEmptyWithContent(): void
    {
        $client = $this->createGitlabClient();
        $project = new GitlabProject($this->getDataJson('gitlab/sample-composer.json'));

        $this->assertFalse($client->isEmpty($project));
    }

    public function testIsEmptyWithEmptyRepo(): void
    {
        $client = $this->createGitlabClient();
        $project = new GitlabProject([
            'id' => '12345',
            'empty_repo' => true,
        ]);

        $this->assertTrue($client->isEmpty($project));
    }

    public function testIsEmptyUndefined(): void
    {
        $client = $this->createGitlabClient();
        $project = new GitlabProject([
            'id' => '12345',
        ]);

        $this->assertFalse($client->isEmpty($project));
    }
}
