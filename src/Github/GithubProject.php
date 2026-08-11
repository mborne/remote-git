<?php

namespace MBO\RemoteGit\Github;

use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\ProjectVisibility;

/**
 * Project implementation for github.
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

    public function getId(): string
    {
        return $this->rawMetadata['id'];
    }

    public function getName(): string
    {
        return $this->rawMetadata['full_name'];
    }

    public function getDescription(): string
    {
        return $this->rawMetadata['description'] ?? '';
    }

    public function getTopics(): array
    {
        return $this->rawMetadata['topics'] ?? [];
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

    public function getVisibility(): ?ProjectVisibility
    {
        return $this->rawMetadata['private'] ? ProjectVisibility::PRIVATE : ProjectVisibility::PUBLIC;
    }

    /**
     * Note that github API doesn't provide an "empty" property, the repository
     * size (in kB) is used to detect repositories without content.
     */
    public function isEmpty(): bool
    {
        return 0 === ($this->rawMetadata['size'] ?? 0);
    }

    public function getRawMetadata(): array
    {
        return $this->rawMetadata;
    }
}
