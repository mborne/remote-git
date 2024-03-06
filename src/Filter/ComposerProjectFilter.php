<?php

namespace MBO\RemoteGit\Filter;

use Psr\Log\LoggerInterface;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ClientInterface as GitClientInterface;
use MBO\RemoteGit\Helper\LoggerHelper;

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
     * @var GitClientInterface
     */
    protected $gitClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Filter according to project type
     *
     * @var string
     */
    protected $projectType;

    /**
     * ProjectTypeFilter constructor.
     *
     * @param LoggerInterface $logger
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(
        GitClientInterface $gitClient,
        LoggerInterface $logger = null
    ) {
        $this->gitClient = $gitClient;
        $this->logger = LoggerHelper::handleNull($logger);
    }

    /**
     * Get filter according to project type
     *
     * @return string
     */
    public function getProjectType()
    {
        return $this->projectType;
    }

    /**
     * Set filter according to project type
     *
     * @param string $projectType Filter according to project type
     *
     * @return self
     */
    public function setProjectType($projectType)
    {
        $this->projectType = $projectType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $description = 'composer.json should exists';
        if (!empty($this->projectType)) {
            $description .= sprintf(" and type should be '%s'", $this->projectType);
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccepted(ProjectInterface $project)
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
