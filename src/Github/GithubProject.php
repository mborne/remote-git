<?php

namespace MBO\RemoteGit\Github;

use MBO\RemoteGit\ProjectInterface;

/**
 * Project implementation for github
 *
 * @author mborne
 */
class GithubProject implements ProjectInterface
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
        return $this->rawMetadata['default_branch'];
    }

    /**
     * {@inheritDoc}
     */
    public function getHttpUrl(): string
    {
        return $this->rawMetadata['clone_url'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRawMetadata(): array
    {
        return $this->rawMetadata;
    }
}
