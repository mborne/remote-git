<?php

namespace MBO\RemoteGit\Exception;

use RuntimeException;

/**
 * Custom exception for missing parameters
 */
class MissingConstException extends RuntimeException
{
    /**
     * @param string $className name of concerned class
     * @param string $constName name of missing const
     */
    public function __construct($className, $constName)
    {
        $message = sprintf("Missing const '%s' on class '%s'", $constName, $className);
        parent::__construct($message);
    }
}
