<?php

namespace MBO\RemoteGit\Exception;

use RuntimeException;

/**
 * Custom exception for missing parameters
 */
class MissingConstException extends RuntimeException
{
    public function __construct($className, $constName)
    {
        $message = sprintf("Missing const '%s' on class '%s'", $constName, $className);
        parent::__construct($message);
    }
}
