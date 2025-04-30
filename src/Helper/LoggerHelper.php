<?php

namespace MBO\RemoteGit\Helper;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Helper class to simplify NullLogger management.
 */
class LoggerHelper
{
    /**
     * Converts null to NullLogger.
     */
    public static function handleNull(
        ?LoggerInterface $logger = null,
    ): LoggerInterface {
        return is_null($logger) ? new NullLogger() : $logger;
    }
}
