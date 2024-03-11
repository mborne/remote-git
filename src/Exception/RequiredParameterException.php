<?php

namespace MBO\RemoteGit\Exception;

use RuntimeException;

/**
 * Custom exception for missing parameters
 */
class RequiredParameterException extends RuntimeException
{
    public function __construct(string $message = 'missing required parameter')
    {
        parent::__construct($message);
    }
}
