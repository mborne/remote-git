<?php

namespace MBO\RemoteGit;

use MBO\RemoteGit\Filter\FilterCollection;

/**
 * Find options to filter project listing
 *
 * @author mborne
 */
class FindOptions
{
    /**
     * Filter according to organizations
     *
     * @var string[]
     */
    private array $organizations = [];

    /**
     * Filter according to user names
     *
     * @var string[]
     */
    private array $users = [];

    /**
     * Search string (available only for gitlab prefer the use of organizations and users)
     */
    private string $search;

    /**
     * Additional filter that can't be implemented throw
     * project listing API parameters
     */
    private ProjectFilterInterface $filter;

    public function __construct()
    {
        $this->filter = new FilterCollection();
    }

    /**
     * True if search is defined
     */
    public function hasSearch(): bool
    {
        return !empty($this->search);
    }

    /**
     * Get filter according to organizations
     *
     * @return string[]
     */
    public function getOrganizations(): array
    {
        return $this->organizations;
    }

    /**
     * Set filter according to organizations
     *
     * @param string[] $organizations Filter according to organizations
     */
    public function setOrganizations(array $organizations): self
    {
        $this->organizations = $organizations;

        return $this;
    }

    /**
     * Get filter according to user names
     *
     * @return string[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * Set filter according to user names
     *
     * @param string[] $users Filter according to user names
     */
    public function setUsers(array $users): self
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get search string (prefer the use of organizations and users)
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * Set search string (prefer the use of organizations and users)
     */
    public function setSearch(string $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Get project listing API parameters
     */
    public function getFilter(): ProjectFilterInterface
    {
        return $this->filter;
    }

    /**
     * Set project listing API parameters
     */
    public function setFilter(ProjectFilterInterface $filter): self
    {
        $this->filter = $filter;

        return $this;
    }
}
