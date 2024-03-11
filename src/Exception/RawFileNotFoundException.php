<?php

namespace MBO\RemoteGit\Exception;

use RuntimeException;
use Throwable;

/**
 * Custom exception for missing raw file
 */
class RawFileNotFoundException extends RuntimeException
{
    public function __construct(
        string $filePath,
        string $ref,
        Throwable $previous = null
    ) {
        $message = sprintf("file '%s' not found on branch '%s'", $filePath, $ref);
        parent::__construct($message, 404, $previous);
    }
}
