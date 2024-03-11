<?php

namespace MBO\RemoteGit\Exception;

/**
 * Custom exception for missing client type.
 */
class ClientNotFoundException extends \RuntimeException
{
    /**
     * @param string[] $availableTypes
     */
    public function __construct(string $typeName, array $availableTypes = [])
    {
        $message = sprintf("type '%s' not found in %s", $typeName, json_encode($availableTypes));
        parent::__construct($message);
    }
}
