<?php

namespace MBO\RemoteGit;

/**
 * Test if a project should be included in satis config (regexp, ).
 *
 * @author mborne
 */
interface ProjectFilterInterface
{
    /**
     * Get filter description (ex : "Project should contains a composer.json file").
     */
    public function getDescription(): string;

    /**
     * Returns true if the project should be included in satis configuration.
     */
    public function isAccepted(ProjectInterface $project): bool;
}
