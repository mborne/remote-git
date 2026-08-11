<?php

namespace MBO\RemoteGit\Tests\Github;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use MBO\RemoteGit\Github\GithubClient;
use MBO\RemoteGit\Github\GithubProject;
use MBO\RemoteGit\Tests\TestCase;

/**
 * Test GithubClient with mocked HTTP responses.
 */
class GithubClientTest extends TestCase
{
    private MockHandler $mockHandler;

    /**
     * Create a client with a queue of mocked responses.
     *
     * @param Response[] $responses
     */
    private function createGithubClient(array $responses = []): GithubClient
    {
        $this->mockHandler = new MockHandler($responses);

        return new GithubClient(new GuzzleHttpClient([
            'base_uri' => 'https://api.github.com',
            'handler' => HandlerStack::create($this->mockHandler),
        ]));
    }

    /**
     * Get the URI requested by the client, null if no request is performed.
     */
    private function getLastRequestUri(): ?string
    {
        $request = $this->mockHandler->getLastRequest();

        return null === $request ? null : (string) $request->getUri();
    }

    /**
     * Create a project reported with a size of 0 by github API.
     */
    private function createEmptySizedProject(): GithubProject
    {
        return new GithubProject([
            'id' => '12345',
            'full_name' => 'mborne/sample-empty',
            'commits_url' => 'https://api.github.com/repos/mborne/sample-empty/commits{/sha}',
            'size' => 0,
        ]);
    }

    /**
     * A repository with a size is not empty, no API call required.
     */
    public function testIsEmptyWithContent(): void
    {
        $client = $this->createGithubClient();
        $project = new GithubProject($this->getDataJson('github/docker-devbox.json'));

        $this->assertFalse($client->isEmpty($project));
        $this->assertNull($this->getLastRequestUri());
    }

    /**
     * github replies "409 Git Repository is empty." when there is no commit.
     */
    public function testIsEmptyWithoutCommit(): void
    {
        $client = $this->createGithubClient([
            new Response(409, [], (string) json_encode([
                'message' => 'Git Repository is empty.',
            ])),
        ]);

        $this->assertTrue($client->isEmpty($this->createEmptySizedProject()));
        $this->assertEquals(
            'https://api.github.com/repos/mborne/sample-empty/commits?per_page=1',
            $this->getLastRequestUri()
        );
    }

    /**
     * A repository smaller than 1 kB is reported with a size of 0 by github
     * while it contains commits.
     */
    public function testIsEmptyWithSmallContent(): void
    {
        $client = $this->createGithubClient([
            new Response(200, [], (string) json_encode([
                ['sha' => '5d4f2c1b'],
            ])),
        ]);

        $this->assertFalse($client->isEmpty($this->createEmptySizedProject()));
        $this->assertEquals(
            'https://api.github.com/repos/mborne/sample-empty/commits?per_page=1',
            $this->getLastRequestUri()
        );
    }

    public function testIsEmptyWithUnexpectedStatusCode(): void
    {
        $client = $this->createGithubClient([
            new Response(404, [], (string) json_encode([
                'message' => 'Not Found',
            ])),
        ]);

        $thrown = null;
        try {
            $client->isEmpty($this->createEmptySizedProject());
        } catch (\Exception $e) {
            $thrown = $e;
        }
        $this->assertNotNull($thrown, 'exception should be thrown');
        $this->assertInstanceOf(\RuntimeException::class, $thrown);
        $this->assertEquals(
            'Unexpected status code 404 for GET https://api.github.com/repos/mborne/sample-empty/commits?per_page=1',
            $thrown->getMessage()
        );
    }
}
