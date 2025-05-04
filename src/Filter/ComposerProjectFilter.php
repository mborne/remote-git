<?php

namespace MBO\RemoteGit\Filter;

use MBO\RemoteGit\ClientInterface as GitClientInterface;
use MBO\RemoteGit\Helper\LoggerHelper;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ProjectInterface;
use Psr\Log\LoggerInterface;

/**
 * Filter projects ensuring that composer.json is present. Optionally,
 * a project type can be forced.
 *
 * @author fantoine
 * @author mborne
 */
class ComposerProjectFilter implements ProjectFilterInterface
{
    /**
     * Client allowing to retrieve composer.json file.
     */
    protected GitClientInterface $gitClient;

    protected LoggerInterface $logger;

    /**
     * Filter according to composer project type ("library", "project",...).
     *
     * @see https://getcomposer.org/doc/04-schema.md#type
     */
    protected string $projectType;

    /**
     * ProjectTypeFilter constructor.
     */
    public function __construct(
        GitClientInterface $gitClient,
        ?LoggerInterface $logger = null,
    ) {
        $this->gitClient = $gitClient;
        $this->logger = LoggerHelper::handleNull($logger);
    }

    /**
     * Get filter according to project type.
     */
    public function getProjectType(): string
    {
        return $this->projectType;
    }

    /**
     * Set filter according to project type. 
     */
    public function setProjectType(string $projectType): self
    {
        $this->projectType = $projectType;

        return $this;
    }

    public function getDescription(): string
    {
        $description = 'composer.json should exists';
        if (!empty($this->projectType)) {
            $description .= sprintf(" and type should be '%s'", $this->projectType);
        }

        return $description;
    }

    public function isAccepted(ProjectInterface $project): bool
    {
        try {
            $branch = $project->getDefaultBranch();
            if (is_null($branch)) {
                return false;
            }
            $json = $this->gitClient->getRawFile(
                $project,
                'composer.json',
                $branch
            );
            $composer = json_decode($json, true);
            if (empty($this->projectType)) {
                return true;
            }
            $types = array_map('strtolower', explode(',', $this->projectType));

            return isset($composer['type'])
                && in_array(strtolower($composer['type']), $types);
        } catch (\Exception $e) {
            $this->logger->debug(sprintf(
                '%s (branch %s) : file %s not found',
                $project->getName(),
                $project->getDefaultBranch(),
                'composer.json'
            ));

            return false;
        }
    }
}
