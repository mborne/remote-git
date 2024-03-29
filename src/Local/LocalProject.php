<?php

namespace MBO\RemoteGit\Local;

use MBO\RemoteGit\ProjectInterface;

/**
 * Project corresponding to a local git folder.
 */
class LocalProject implements ProjectInterface
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
        return $this->rawMetadata['head_branch'];
    }

    public function getHttpUrl(): string
    {
        return $this->rawMetadata['full_path'];
    }

    public function getRawMetadata(): array
    {
        return $this->rawMetadata;
    }
}
