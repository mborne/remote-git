<?php

namespace MBO\RemoteGit\Http;

/**
 * Provides types of implementation for token
 */
class TokenType
{
    public const NONE = 'none';
    public const PRIVATE_TOKEN = 'Private-Token: {token}';
    public const AUTHORIZATION_TOKEN = 'Authorization: token {token}';

    /**
     * Create HTTP headers according to a tokenType
     *
     * @return array<string,string>
     */
    public static function createHttpHeaders(string $tokenType, ?string $token): array
    {
        if (empty($token)) {
            return [];
        }

        $parts = explode(': ', $tokenType);

        return [
            $parts[0] => str_replace('{token}', $token, $parts[1]),
        ];
    }
}
