<?php

namespace MBO\RemoteGit\Filter;

use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ProjectInterface;

/**
 * Ignore project if project.name matches a given regular expression.
 *
 * @author mborne
 */
class IgnoreRegexpFilter implements ProjectFilterInterface
{
    protected string $ignoreRegexp;

    public function __construct(string $ignoreRegexp)
    {
        assert(!empty($ignoreRegexp));
        $this->ignoreRegexp = $ignoreRegexp;
    }

    public function getDescription(): string
    {
        return 'project name should not match /'.$this->ignoreRegexp.'/';
    }

    public function isAccepted(ProjectInterface $project): bool
    {
        return !preg_match("/$this->ignoreRegexp/", $project->getName());
    }
}
