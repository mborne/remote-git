<?php

namespace MBO\RemoteGit\Helper;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Helper class to simplify NullLogger management
 */
class LoggerHelper
{
    /**
     * Converts null to NullLogger
     *
     * @param LoggerInterface $logger
     *
     * @return LoggerInterface
     */
    public static function handleNull(
        LoggerInterface $logger = null
    ) {
        return is_null($logger) ? new NullLogger() : $logger;
    }
}
