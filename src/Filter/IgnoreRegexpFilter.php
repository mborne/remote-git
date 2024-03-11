<?php

namespace MBO\RemoteGit\Filter;

use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\ProjectFilterInterface;

/**
 * Ignore project if project.name matches a given regular expression
 *
 * @author mborne
 */
class IgnoreRegexpFilter implements ProjectFilterInterface
{
    /**
     * @var string
     */
    protected string $ignoreRegexp;

    public function __construct(string $ignoreRegexp)
    {
        assert(!empty($ignoreRegexp));
        $this->ignoreRegexp = $ignoreRegexp;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return 'project name should not match /'.$this->ignoreRegexp.'/';
    }

    /**
     * {@inheritDoc}
     */
    public function isAccepted(ProjectInterface $project): bool
    {
        return !preg_match("/$this->ignoreRegexp/", $project->getName());
    }
}
