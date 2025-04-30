<?php

namespace MBO\RemoteGit\Gogs;

use MBO\RemoteGit\ProjectInterface;

/**
 * Project implementation for github.
 *
 * @author mborne
 */
class GogsProject implements ProjectInterface
{
    /**
     * @param array<string,mixed> $rawMetadata
     */
    public function __construct(private array $rawMetadata)
    {
    }

    public function getId(): string
    {
        return $this->rawMetadata['id'];
    }

    public function getName(): string
    {
        return $this->rawMetadata['full_name'];
    }

    public function getDefaultBranch(): ?string
    {
        return $this->rawMetadata['default_branch'];
    }

    public function getHttpUrl(): string
    {
        return $this->rawMetadata['clone_url'];
    }

    public function isArchived(): bool
    {
        return $this->rawMetadata['archived'];
    }

    public function getRawMetadata(): array
    {
        return $this->rawMetadata;
    }
}
