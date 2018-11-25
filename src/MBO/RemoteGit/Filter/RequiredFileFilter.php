<?php

namespace MBO\RemoteGit\Filter;

use Psr\Log\LoggerInterface;

use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\ClientInterface as GitClientInterface;
use MBO\RemoteGit\Helper\LoggerHelper;


/**
 * Accept projects if git repository contains a given file in default branch
 * 
 * @author mborne
 */
class RequiredFileFilter implements ProjectFilterInterface {

    /**
     * @var GitClientInterface
     */
    protected $gitClient;

    /**
     * @var string
     */
    protected $filePath;

    public function __construct(
        GitClientInterface $gitClient,
        $filePath,
        LoggerInterface $logger = null
    )
    {
        $this->gitClient = $gitClient;
        $this->filePath  = $filePath;
        $this->logger    = LoggerHelper::handleNull($logger);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(){
        return sprintf("File '%s' should exist in default branch",$this->filePath);
    }


    /**
     * {@inheritDoc}
     */
    public function isAccepted(ProjectInterface $project)
    {
        try {
            $this->gitClient->getRawFile(
                $project,
                $this->filePath,
                $project->getDefaultBranch()
            );
            return true;
        }catch(\Exception $e){
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
