<?php

namespace MBO\RemoteGit\Exception;

use RuntimeException;

/**
 * Custom exception for missing client type
 */
class ClientNotFoundException extends RuntimeException
{
    public function __construct($typeName, $availableTypes = [])
    {
        $message = sprintf("type '%s' not found in %s", $typeName, json_encode($availableTypes));
        parent::__construct($message);
    }
}
