<?php

namespace MBO\RemoteGit\Tests;

use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\ClientOptions;
use MBO\RemoteGit\Exception\ClientNotFoundException;
use MBO\RemoteGit\Github\GithubClient;
use MBO\RemoteGit\Gitlab\GitlabClient;
use MBO\RemoteGit\Gogs\GogsClient;
use MBO\RemoteGit\Local\LocalClient;

class ClientFactoryTest extends TestCase
{
    public function testGetTypes(): void
    {
        $clientFactory = ClientFactory::getInstance();

        $types = $clientFactory->getTypes();
        $this->assertCount(4, $types);
    }

    public function testInvalidType(): void
    {
        $clientFactory = ClientFactory::getInstance();
        $thrown = null;
        try {
            $options = new ClientOptions();
            $options->setType('missing');
            $clientFactory->createGitClient($options);
        } catch (\Exception $e) {
            $thrown = $e;
        }
        $this->assertNotNull($thrown, 'exception should be thrown');
        $this->assertInstanceOf(ClientNotFoundException::class, $thrown);
        $this->assertStringStartsWith(
            "type 'missing' not found in",
            $thrown->getMessage()
        );
    }

    public function testDetectClientType(): void
    {
        $this->assertEquals(
            GithubClient::class,
            ClientFactory::detectClientClass('https://github.com')
        );
        $this->assertEquals(
            GogsClient::class,
            ClientFactory::detectClientClass('https://gogs.localhost')
        );
        $this->assertEquals(
            GogsClient::class,
            ClientFactory::detectClientClass('http://gogs.forge.fr')
        );

        $this->assertEquals(
            LocalClient::class,
            ClientFactory::detectClientClass('/path/to/a/folder')
        );

        // fallback to gitlab for satis-gitlab original implementation
        $this->assertEquals(
            GitlabClient::class,
            ClientFactory::detectClientClass('https://something-else.com')
        );
    }
}
