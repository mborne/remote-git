<?php

namespace MBO\RemoteGit\Gitlab;

use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\ProjectVisibility;
use RuntimeException;

/**
 * Common project properties between different git project host (gitlab, github, etc.).
 *
 * @author mborne
 */
class GitlabProject implements ProjectInterface
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
        return $this->rawMetadata['path_with_namespace'];
    }

    public function getDefaultBranch(): ?string
    {
        if (!isset($this->rawMetadata['default_branch'])) {
            return null;
        }

        return $this->rawMetadata['default_branch'];
    }

    public function getHttpUrl(): string
    {
        return $this->rawMetadata['http_url_to_repo'];
    }

    public function isArchived(): bool
    {
        return $this->rawMetadata['archived'];
    }

    public function getVisibility(): ?ProjectVisibility
    {
        switch ($this->rawMetadata['visibility']) {
            case 'public':
                return ProjectVisibility::PUBLIC;
            case 'private':
                return ProjectVisibility::PRIVATE;
            case 'internal':
                return ProjectVisibility::INTERNAL;
            default:
                throw new RuntimeException("Unknown visibility: {$this->rawMetadata['visibility']}");
        }
    }

    public function getRawMetadata(): array
    {
        return $this->rawMetadata;
    }
}
