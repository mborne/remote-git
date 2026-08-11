<?php

namespace MBO\RemoteGit\Tests\Gogs;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use MBO\RemoteGit\Gogs\GogsClient;
use MBO\RemoteGit\Gogs\GogsProject;
use MBO\RemoteGit\Tests\TestCase;

/**
 * Test GogsClient without API call, gitea and gogs providing "empty"
 * in the project metadata.
 */
class GiteaClientTest extends TestCase
{
    /**
     * Create a client which fails if an API call is performed.
     */
    private function createGogsClient(): GogsClient
    {
        return new GogsClient(new GuzzleHttpClient([
            'base_uri' => 'https://gitea.com',
            'handler' => HandlerStack::create(new MockHandler()),
        ]));
    }

    public function testIsEmptyWithContent(): void
    {
        $client = $this->createGogsClient();
        $project = new GogsProject($this->getDataJson('gitea/sample-composer.json'));

        $this->assertFalse($client->isEmpty($project));
    }

    public function testIsEmptyWithEmptyRepo(): void
    {
        $client = $this->createGogsClient();
        $project = new GogsProject([
            'id' => '12345',
            'empty' => true,
        ]);

        $this->assertTrue($client->isEmpty($project));
    }

    public function testIsEmptyUndefined(): void
    {
        $client = $this->createGogsClient();
        $project = new GogsProject([
            'id' => '12345',
        ]);

        $this->assertFalse($client->isEmpty($project));
    }
}
