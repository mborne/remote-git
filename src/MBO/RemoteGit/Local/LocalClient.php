<?php

namespace MBO\RemoteGit\Local;

use Psr\Log\LoggerInterface;
use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Helper\LoggerHelper;
use MBO\RemoteGit\ProjectInterface;
use Symfony\Component\Finder\Finder;

/**
 * Client for a local folder containing a project hierarchy
 */
class LocalClient implements ClientInterface {

    /**
     * Path to the root folder
     * @var string
     */
    private $rootPath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $rootPath
     * @param LoggerInterface $logger
     */
    public function __construct($rootPath, LoggerInterface $logger = null)
    {
        $this->rootPath = realpath($rootPath);
        $this->logger   = LoggerHelper::handleNull($logger);
    }

    /**
     * {@inheritDoc}
     */
    public function find(FindOptions $options){
        $projects = [];

        $projectFolders = [];
        $this->findProjectFolders($this->rootPath,$projectFolders);
        foreach ( $projectFolders as $projectFolder ){
            $project = $this->createLocalProject($projectFolder);
            // TODO filters
            $projects[] = $project;
        }

        return $projects;
    }

    /**
     * Create a LocalProject retreiving metadata from absolute path to project
     *
     * @param string $projectFolder
     * @return LocalProject
     */
    public function createLocalProject($projectFolder){
        /* relativize path and remove .git for bare repositories */
        $fullName = substr($projectFolder,strlen($this->rootPath)+1);
        $isBare   = false ;
        if ( '.git' === substr($projectFolder, -4 ) ){
            $isBare = true;
            $fullName = substr($fullName,0,strlen($fullName)-4);
        }
        // TODO remove trailing .git for bare repositories
        $rawMetadata = [
            'id' => sha1($projectFolder),
            'is_bare'   => $isBare,
            'full_path' => $projectFolder,
            'full_name' => $fullName,
            'head_branch' => 'master' // TODO
        ];
        return new LocalProject($rawMetadata);
    }

    /**
     * Retreive absolute path to project folders.
     *
     * TODO use something like "git rev-parse --git-dir" to validate
     * folders
     *
     * @param string $parentPath absolute path to a given folder
     * @param array $projectFolders
     * @return string[]
     */
    protected function findProjectFolders($parentPath,array &$projectFolders){
        $this->logger->info("look for .git folder in $parentPath ...");
        $items = scandir($parentPath);
        foreach ( $items as $item ){
            if ( '.' === $item || '..' === $item ){
                continue;
            }
            $itemPath = $parentPath.DIRECTORY_SEPARATOR.$item;
            if ( ! is_dir($itemPath) ){
                continue;
            }
            if ( '.git' === $item ){
                /* non bare repository containing a .git directory */
                $projectFolders[] = $parentPath;
            }elseif ( '.git' === substr($parentPath, -4) ){
                /* bare repository with folder name ending with .git */
                $projectFolders[] = $parentPath;
            }else{
                /* recursive search */
                $this->findProjectFolders($itemPath,$projectFolders);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRawFile(
        ProjectInterface $project,
        $filePath,
        $ref
    ){
        $cmd  = sprintf(
            'cd %s ; git show %s:%s',
            escapeshellarg($project->getHttpUrl()),
            escapeshellarg($ref),
            escapeshellarg($filePath)
        );
        return shell_exec($cmd);
    }


}

