<?php

namespace MBO\RemoteGit\Local;

use MBO\RemoteGit\ProjectInterface;

/**
 * Project corresponding to a local git folder
 */
class LocalProject implements ProjectInterface {

    /**
     * @var string[]
     */
    protected $rawMetadata;

    public function __construct(array $rawMetadata)
    {
        $this->rawMetadata = $rawMetadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getId(){
        return $this->rawMetadata['id'];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(){
        return $this->rawMetadata['full_name'];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultBranch(){
        return $this->rawMetadata['head_branch'];
    }

    /**
     * {@inheritDoc}
     */
    public function getHttpUrl(){
        return $this->rawMetadata['full_path'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRawMetadata(){
        return $this->rawMetadata;
    }

}