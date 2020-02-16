<?php

namespace MBO\RemoteGit\Local;

use MBO\RemoteGit\ProjectInterface;

/**
 * Project corresponding to a local git folder
 */
class LocalProject implements ProjectInterface
{
    /**
     * @var string[]
     */
    protected $rawMetadata;

    public function __construct(array $rawMetadata)
    {
        $this->rawMetadata = $rawMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->rawMetadata['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->rawMetadata['full_name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBranch()
    {
        return $this->rawMetadata['head_branch'];
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpUrl()
    {
        return $this->rawMetadata['full_path'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRawMetadata()
    {
        return $this->rawMetadata;
    }
}
