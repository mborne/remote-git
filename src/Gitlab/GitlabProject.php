<?php

namespace MBO\RemoteGit\Gitlab;

use MBO\RemoteGit\ProjectInterface;

/**
 * Common project properties between different git project host (gitlab, github, etc.)
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
        return $this->rawMetadata['path_with_namespace'];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultBranch(): ?string
    {
        if (!isset($this->rawMetadata['default_branch'])) {
            return null;
        }

        return $this->rawMetadata['default_branch'];
    }

    /**
     * {@inheritDoc}
     */
    public function getHttpUrl(): string
    {
        return $this->rawMetadata['http_url_to_repo'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRawMetadata(): array
    {
        return $this->rawMetadata;
    }
}
