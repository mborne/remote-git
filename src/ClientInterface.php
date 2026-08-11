<?php

namespace MBO\RemoteGit;

/**
 * Lightweight client interface to list hosted git project
 * and access files such as composer.json.
 *
 * @author mborne
 */
interface ClientInterface
{
    /**
     * Find projects according to options.
     *
     * @return iterable<ProjectInterface>
     */
    public function getProjects(FindOptions $options): iterable;

    /**
     * Find projects using API calls.
     *
     * @deprecated use getProjects instead
     *
     * @return ProjectInterface[]
     */
    public function find(FindOptions $options): array;

    /**
     * Indicates if the repository is empty (no commit).
     *
     * Note that an API call may be performed as some hosts don't provide this
     * information in the project metadata.
     */
    public function isEmpty(ProjectInterface $project): bool;

    /**
     * Get raw file.
     *
     * @param ProjectInterface $project  ex : 123456
     * @param string           $filePath ex : composer.json
     * @param string           $ref      ex : master
     */
    public function getRawFile(
        ProjectInterface $project,
        string $filePath,
        string $ref,
    ): string;
}
