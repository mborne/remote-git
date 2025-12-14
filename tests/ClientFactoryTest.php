<?php

namespace MBO\RemoteGit\Tests;

use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\ClientOptions;
use MBO\RemoteGit\Exception\ClientNotFoundException;
use MBO\RemoteGit\Exception\ProtocolNotSupportedException;
use MBO\RemoteGit\Github\GithubClient;
use MBO\RemoteGit\Gitlab\GitlabClient;
use MBO\RemoteGit\Gogs\GogsClient;

class ClientFactoryTest extends TestCase
{
    public function testGetTypes(): void
    {
        $clientFactory = ClientFactory::getInstance();

        $types = $clientFactory->getTypes();
        $this->assertCount(3, $types);
    }

    public function testHasType(): void
    {
        $clientFactory = ClientFactory::getInstance();

        $this->assertTrue($clientFactory->hasType('github'));
        $this->assertFalse($clientFactory->hasType('type-not-found'));
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
            GogsClient::class,
            ClientFactory::detectClientClass('https://gitea.com')
        );
        $this->assertEquals(
            GogsClient::class,
            ClientFactory::detectClientClass('https://gitea.example.com')
        );

        // fallback to gitlab for satis-gitlab original implementation
        $this->assertEquals(
            GitlabClient::class,
            ClientFactory::detectClientClass('https://something-else.com')
        );
    }

    public function testRemovedLocalClient(): void
    {
        $thrownException = null;
        try {
            ClientFactory::detectClientClass('/path/to/a/folder');
        } catch (\Exception $e) {
            $thrownException = $e;
        }
        $this->assertNotNull($thrownException);
        $this->assertInstanceOf(ProtocolNotSupportedException::class, $thrownException);
        $this->assertEquals(
            "protocol 'file' for '/path/to/a/folder' is not supported (LocalClient has been removed)",
            $thrownException->getMessage()
        );
    }

    public function testGitProtocol(): void
    {
        $thrownException = null;
        try {
            ClientFactory::detectClientClass('git://github.com/mborne');
        } catch (\Exception $e) {
            $thrownException = $e;
        }
        $this->assertNotNull($thrownException);
        $this->assertInstanceOf(ProtocolNotSupportedException::class, $thrownException);
        $this->assertEquals(
            "protocol 'git' for 'git://github.com/mborne' is not supported (use HTTPS)",
            $thrownException->getMessage()
        );
    }
}
