<?php

namespace MBO\RemoteGit\Local;

use MBO\RemoteGit\ProjectInterface;

/**
 * Project corresponding to a local git folder
 */
class LocalProject implements ProjectInterface
{
    /**
     * @param array<string,mixed> $rawMetadata
     */
    public function __construct(private array $rawMetadata)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return $this->rawMetadata['id'];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->rawMetadata['full_name'];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultBranch(): ?string
    {
        return $this->rawMetadata['head_branch'];
    }

    /**
     * {@inheritDoc}
     */
    public function getHttpUrl(): string
    {
        return $this->rawMetadata['full_path'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRawMetadata(): array
    {
        return $this->rawMetadata;
    }
}
