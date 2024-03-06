<?php

namespace MBO\RemoteGit\Tests\Http;

use MBO\RemoteGit\Tests\TestCase;
use MBO\RemoteGit\Http\TokenType;

class TokenTypeTest extends TestCase
{
    public function testCreateHttpHeaders(): void
    {
        // empty headers for null token
        $this->assertEquals(
            [],
            TokenType::createHttpHeaders(TokenType::AUTHORIZATION_TOKEN, null)
        );
        // empty headers for empty token
        $this->assertEquals(
            [],
            TokenType::createHttpHeaders(TokenType::AUTHORIZATION_TOKEN, '')
        );

        $this->assertEquals(
            ['Authorization' => 'token s3cr3t'],
            TokenType::createHttpHeaders(TokenType::AUTHORIZATION_TOKEN, 's3cr3t')
        );
        $this->assertEquals(
            ['Private-Token' => 's3cr3t'],
            TokenType::createHttpHeaders(TokenType::PRIVATE_TOKEN, 's3cr3t')
        );
    }
}
