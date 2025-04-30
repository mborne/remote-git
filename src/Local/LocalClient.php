<?php

namespace MBO\RemoteGit\Local;

use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\Exception\RawFileNotFoundException;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Helper\LoggerHelper;
use MBO\RemoteGit\Http\TokenType;
use MBO\RemoteGit\ProjectInterface;
use Psr\Log\LoggerInterface;

/**
 * Client for a local folder containing a project hierarchy.
 */
class LocalClient implements ClientInterface
{
    public const TYPE = 'local';
    public const TOKEN_TYPE = TokenType::NONE;

    /**
     * Path to the root folder.
     *
     * @var string
     */
    private $rootPath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Create a LocalClient for a folder containing a hierarchy of git repositories.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(string $rootPath, ?LoggerInterface $logger = null)
    {
        $this->rootPath = $rootPath;
        $this->logger = LoggerHelper::handleNull($logger);
    }

    public function find(FindOptions $options): array
    {
        $projects = [];

        $projectFolders = [];
        $this->findProjectFolders($this->rootPath, $projectFolders);
        foreach ($projectFolders as $projectFolder) {
            $project = $this->createLocalProject($projectFolder);
            if (!$options->getFilter()->isAccepted($project)) {
                continue;
            }
            $projects[] = $project;
        }

        return $projects;
    }

    /**
     * Create a LocalProject retreiving metadata from absolute path to project.
     *
     * @param string $projectFolder
     *
     * @return LocalProject
     */
    public function createLocalProject($projectFolder)
    {
        /* relativize path and remove .git for bare repositories */
        $fullName = substr($projectFolder, strlen($this->rootPath) + 1);
        $isBare = false;
        if ('.git' === substr($projectFolder, -4)) {
            $isBare = true;
            $fullName = substr($fullName, 0, strlen($fullName) - 4);
        }
        // TODO remove trailing .git for bare repositories
        $rawMetadata = [
            'id' => sha1($projectFolder),
            'is_bare' => $isBare,
            'full_path' => $projectFolder,
            'full_name' => $fullName,
            'head_branch' => 'master', // TODO
        ];

        return new LocalProject($rawMetadata);
    }

    /**
     * Retreive absolute path to project folders.
     *
     * TODO use something like "git rev-parse --git-dir" to validate
     * folders
     *
     * @param string   $parentPath     absolute path to a given folder
     * @param string[] $projectFolders
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function findProjectFolders(string $parentPath, array &$projectFolders): void
    {
        $this->logger->debug("Checking if $parentPath is a git repository ...");
        $items = scandir($parentPath);
        assert(false !== $items);
        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }
            $itemPath = $parentPath.DIRECTORY_SEPARATOR.$item;
            if (!is_dir($itemPath)) {
                continue;
            }
            if ('.git' === $item) {
                /* non bare repository containing a .git directory */
                $this->logger->info(sprintf('Git repository found : %s', $parentPath));
                $projectFolders[] = $parentPath;
            } elseif ('.git' === substr($itemPath, -4)) {
                /* bare repository with folder name ending with .git */
                $this->logger->info(sprintf('Git bare repository found : %s', $itemPath));
                $projectFolders[] = $itemPath;
            } else {
                /* recursive search */
                $this->findProjectFolders($itemPath, $projectFolders);
            }
        }
    }

    public function getRawFile(
        ProjectInterface $project,
        string $filePath,
        string $ref,
    ): string {
        $cmd = sprintf(
            'git show %s:%s',
            escapeshellarg($ref),
            escapeshellarg($filePath)
        );
        $cwd = $project->getHttpUrl();

        $pipes = [];
        $proc = proc_open($cmd, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes, $cwd);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        if (false === $proc || 0 !== proc_close($proc)) {
            $this->logger->error(sprintf('command fails : %s', $stderr), [
                'cmd' => $cmd,
                'cwd' => $cwd,
            ]);
            throw new RawFileNotFoundException($filePath, $ref);
        }

        assert(false !== $stdout);

        return $stdout;
    }
}
