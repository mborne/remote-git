<?php

namespace MBO\RemoteGit\Filter;

use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ProjectInterface;

/**
 * Ignore project if project.name doesn't match a given regular expression.
 *
 * @author mborne
 */
class IncludeRegexpFilter implements ProjectFilterInterface
{
    protected string $includeRegexp;

    public function __construct(string $includeRegexp)
    {
        assert(!empty($includeRegexp));
        $this->includeRegexp = $includeRegexp;
    }

    public function getDescription(): string
    {
        return 'project name should match /'.$this->includeRegexp.'/';
    }

    public function isAccepted(ProjectInterface $project): bool
    {
        return 0 !== preg_match("/$this->includeRegexp/", $project->getName());
    }
}
