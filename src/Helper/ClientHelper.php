<?php

namespace MBO\RemoteGit\Helper;

use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\Exception\MissingConstException;
use MBO\RemoteGit\Exception\RequiredParameterException;

/**
 * Helper to inspect client classes.
 */
class ClientHelper
{
    /**
     * Retrieve TYPE and TOKEN_TYPE from client class.
     *
     * @param class-string $className
     *
     * @return string[]
     */
    public static function getStaticProperties(string $className): array
    {
        $reflectionClass = new \ReflectionClass($className);
        if (!$reflectionClass->implementsInterface(ClientInterface::class)) {
            throw new RequiredParameterException(sprintf('%s must implement %s', $className, ClientInterface::class));
        }
        /* retrieve TYPE */
        $typeName = $reflectionClass->getConstant('TYPE');
        if (empty($typeName)) {
            throw new MissingConstException($className, 'TYPE');
        }
        /* retrieve TOKEN_TYPE */
        $tokenType = $reflectionClass->getConstant('TOKEN_TYPE');
        if (empty($tokenType)) {
            throw new MissingConstException($className, 'TOKEN_TYPE');
        }

        return [
            'typeName' => $typeName,
            'className' => $className,
            'tokenType' => $tokenType,
        ];
    }
}
