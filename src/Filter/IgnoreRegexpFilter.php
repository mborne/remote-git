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
    protected $ignoreRegexp;

    public function __construct($ignoreRegexp)
    {
        assert(!empty($ignoreRegexp));
        $this->ignoreRegexp = $ignoreRegexp;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'project name should not match /'.$this->ignoreRegexp + '/';
    }

    /**
     * {@inheritdoc}
     */
    public function isAccepted(ProjectInterface $project)
    {
        return !preg_match("/$this->ignoreRegexp/", $project->getName());
    }
}
