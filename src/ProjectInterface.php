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
     * Get project description from API.
     */
    public function getDescription(): string;

    /**
     * Get default branch.
     */
    public function getDefaultBranch(): ?string;

    /**
     * Get http url.
     */
    public function getHttpUrl(): string;

    /**
     * True if the project is archived.
     */
    public function isArchived(): bool;

    /**
     * Get project visibility.
     */
    public function getVisibility(): ?ProjectVisibility;

    /**
     * Get hosting service specific properties.
     *
     * @return array<string,mixed>
     */
    public function getRawMetadata(): array;
}
