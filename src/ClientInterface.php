<?php

namespace MBO\RemoteGit;

/**
 * Lightweight client interface to list hosted git project
 * and access files such as composer.json
 *
 * @author mborne
 */
interface ClientInterface
{
    /**
     * Find projects throw API
     *
     * @return ProjectInterface[]
     */
    public function find(FindOptions $options): array;

    /**
     * Get raw file
     *
     * @param ProjectInterface $project  ex : 123456
     * @param string           $filePath ex : composer.json
     * @param string           $ref      ex : master
     */
    public function getRawFile(
        ProjectInterface $project,
        string $filePath,
        string $ref
    ): string;
}
