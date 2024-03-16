<?php

namespace MBO\RemoteGit\Filter;

use MBO\RemoteGit\ClientInterface as GitClientInterface;
use MBO\RemoteGit\Helper\LoggerHelper;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ProjectInterface;
use Psr\Log\LoggerInterface;

/**
 * Accept projects if git repository contains a given file in default branch.
 *
 * @author mborne
 */
class RequiredFileFilter implements ProjectFilterInterface
{
    /**
     * @var GitClientInterface
     */
    protected $gitClient;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string          $filePath
     * @param LoggerInterface $logger
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(
        GitClientInterface $gitClient,
        $filePath,
        LoggerInterface $logger = null
    ) {
        $this->gitClient = $gitClient;
        $this->filePath = $filePath;
        $this->logger = LoggerHelper::handleNull($logger);
    }

    public function getDescription(): string
    {
        return sprintf("File '%s' should exist in default branch", $this->filePath);
    }

    public function isAccepted(ProjectInterface $project): bool
    {
        $branch = $project->getDefaultBranch();
        if (is_null($branch)) {
            return false;
        }
        try {
            $this->gitClient->getRawFile(
                $project,
                $this->filePath,
                $branch
            );

            return true;
        } catch (\Exception $e) {
            $this->logger->debug(sprintf(
                '%s (branch %s) : file %s not found',
                $project->getName(),
                $project->getDefaultBranch(),
                $this->filePath
            ));

            return false;
        }
    }
}
