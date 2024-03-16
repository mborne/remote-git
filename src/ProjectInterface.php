<?php

namespace MBO\RemoteGit;

/**
 * Common project properties between different git project host (gitlab, github, etc.).
 *
 * @author mborne
 */
interface ProjectInterface
{
    /**
     * Get project id.
     */
    public function getId(): string;

    /**
     * Get project name (with namespace).
     */
    public function getName(): string;

    /**
     * Get default branch.
     */
    public function getDefaultBranch(): ?string;

    /**
     * Get http url.
     */
    public function getHttpUrl(): string;

    /**
     * Get hosting service specific properties.
     *
     * @return array<string,mixed>
     */
    public function getRawMetadata(): array;
}
