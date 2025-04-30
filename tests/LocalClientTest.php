<?php

namespace MBO\RemoteGit\Tests;

use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\ClientOptions;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Local\LocalClient;
use MBO\RemoteGit\Local\LocalProject;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

class LocalClientTest extends TestCase
{
    public const TEMP_DIR = '/tmp/remote-git-test';

    /**
     * Clone some projects in /tmp/remote-git-test to perform functional tests.
     */
    public static function setUpBeforeClass(): void
    {
        $fs = new Filesystem();
        if ($fs->exists(self::TEMP_DIR)) {
            return;
        }
        $fs->mkdir(self::TEMP_DIR);
        $fs->mkdir(self::TEMP_DIR.'/mborne');
        // non bare repository
        exec('cd '.self::TEMP_DIR.'/mborne && git clone https://github.com/mborne/remote-git.git');
        // bare repository
        exec('cd '.self::TEMP_DIR.'/mborne && git clone --bare https://github.com/mborne/satis-gitlab.git');
    }

    /**
     * Create a LocalClient for sample test directory.
     */
    protected function createLocalClient(): LocalClient
    {
        // folder containing mborne/remote-git and mborne/satis-gitlab
        $clientOptions = new ClientOptions();
        $clientOptions
            ->setUrl(self::TEMP_DIR)
        ;

        /* create client */
        $client = ClientFactory::createClient(
            $clientOptions,
            new NullLogger()
        );
        $this->assertInstanceOf(LocalClient::class, $client);

        return $client;
    }

    /**
     * Find all projects in test folder.
     *
     * @return LocalProject[]
     */
    protected function findAllProjects(): array
    {
        /* create client */
        $client = $this->createLocalClient();

        /* search projects */
        $options = new FindOptions();
        $projects = $client->find($options);
        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(LocalProject::class, $project);
            $this->assertGettersWorks($project);
            $projectsByName[$project->getName()] = $project;
        }

        return $projectsByName;
    }

    /**
     * Ensure that mborne/remote-git and mborne/satis-gitlab are found.
     */
    public function testFindAll(): void
    {
        $projectsByName = $this->findAllProjects();
        $this->assertArrayHasKey(
            'mborne/remote-git',
            $projectsByName,
            'Found : '.json_encode(array_keys($projectsByName), JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Ensure that isArchived returns false.
     */
    public function testIsArchived(): void
    {
        $projects = $this->findAllProjects();
        foreach ($projects as $project) {
            $this->assertFalse($project->isArchived());
        }
    }

    /**
     * Ensure that isArchived returns public.
     */
    public function testGetVisibility(): void
    {
        $projects = $this->findAllProjects();
        foreach ($projects as $project) {
            $this->assertNull($project->getVisibility());
        }
    }

    /**
     * Check that raw file content can be retreived from non bare repository.
     */
    public function testGetRawFileFromNonBareRepository(): void
    {
        /* test getRawFile */
        $client = $this->createLocalClient();
        $project = $client->createLocalProject(self::TEMP_DIR.'/mborne/remote-git');
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);
        $readmeContent = $client->getRawFile($project, 'README.md', $defaultBranch);
        $this->assertStringContainsString('# mborne/remote-git', $readmeContent);

        /* test getRawFile not found */
        $this->ensureThatRawFileNotFoundThrowsException($client, $project);
    }

    /**
     * Check that raw file content can be retreived from bare repository.
     */
    public function testGetRawFileFromBareRepository(): void
    {
        /* test getRawFile */
        $client = $this->createLocalClient();
        $project = $client->createLocalProject(self::TEMP_DIR.'/mborne/satis-gitlab.git');
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);
        $readmeContent = $client->getRawFile($project, 'composer.json', $defaultBranch);
        $this->assertStringContainsString('symfony/console', $readmeContent);

        /* test getRawFile not found */
        $this->ensureThatRawFileNotFoundThrowsException($client, $project);
    }
}
