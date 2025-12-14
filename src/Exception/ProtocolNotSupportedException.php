<?php

namespace MBO\RemoteGit\Exception;

/**
 * Custom exception for non http/https URL.
 */
class ProtocolNotSupportedException extends \RuntimeException
{
    public function __construct(string $scheme, string $url)
    {
        // handle removed LocalClient
        $note = 'file' == $scheme ? 'LocalClient has been removed' : 'use HTTPS';
        $message = sprintf("protocol '%s' for '%s' is not supported (%s)", $scheme, $url, $note);
        parent::__construct($message);
    }
}
