<?php

namespace MBO\RemoteGit\Exception;

use RuntimeException;

/**
 * Custom exception for missing parameters
 */
class RequiredParameterException extends RuntimeException
{
    /**
     * @param string $message
     */
    public function __construct($message = 'missing required parameter')
    {
        parent::__construct($message);
    }
}
