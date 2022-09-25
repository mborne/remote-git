<?php

namespace MBO\RemoteGit\Tests;

use Psr\Log\NullLogger;
use MBO\RemoteGit\ClientOptions;
use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Local\LocalClient;
use MBO\RemoteGit\Local\LocalProject;
use Symfony\Component\Filesystem\Filesystem;

class LocalClientTest extends TestCase
{
    const TEMP_DIR = '/tmp/remote-git-test';

    /**
     * Clone some projects in /tmp/remote-git-test to perform functional tests
     */
    public static function setUpBeforeClass(): void
    {
        $fs = new Filesystem();
        if ($fs->exists(self::TEMP_DIR)) {
            return;
        }
        $fs->mkdir(self::TEMP_DIR);
        $fs->mkdir(self::TEMP_DIR.'/mborne');
        exec('cd '.self::TEMP_DIR.'/mborne && git clone https://github.com/mborne/remote-git.git');
        exec('cd '.self::TEMP_DIR.'/mborne && git clone --bare https://github.com/mborne/satis-gitlab.git');
    }

    /**
     * Create a LocalClient for sample test directory
     *
     * @return LocalClient
     */
    protected function createLocalClient()
    {
        // folder containing mborne/remote-git and mborne/satis-gitlab
        $rootPath = realpath(self::TEMP_DIR);

        $clientOptions = new ClientOptions();
        $clientOptions
            ->setUrl($rootPath)
        ;

        /* create client */
        return ClientFactory::createClient(
            $clientOptions,
            new NullLogger()
        );
    }

    /**
     * Find all projects in test folder
     *
     * @return LocalProject[]
     */
    protected function findAllProjects()
    {
        /* create client */
        $client = $this->createLocalClient();
        $this->assertInstanceOf(LocalClient::class, $client);

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
     * Ensure that mborne/remote-git and mborne/satis-gitlab are found
     */
    public function testFindAll()
    {
        $projectsByName = $this->findAllProjects();
        $this->assertArrayHasKey(
            'mborne/remote-git',
            $projectsByName,
            'Found : '.json_encode(array_keys($projectsByName), JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Check that raw file content can be retreived from non bare repository
     */
    public function testGetRawFileFromNonBareRepository()
    {
        $client = $this->createLocalClient();
        $project = $client->createLocalProject(self::TEMP_DIR.'/mborne/remote-git');
        $readmeContent = $client->getRawFile($project, 'README.md', $project->getDefaultBranch());
        $this->assertStringContainsString('# mborne/remote-git', $readmeContent);

        $testCaseContent = $client->getRawFile($project, 'tests/TestCase.php', $project->getDefaultBranch());
        $this->assertStringContainsString('class TestCase', $testCaseContent);
    }

    /**
     * Check that raw file content can be retreived from bare repository
     */
    public function testGetRawFileFromBareRepository()
    {
        $client = $this->createLocalClient();
        $project = $client->createLocalProject(self::TEMP_DIR.'/mborne/satis-gitlab.git');
        $readmeContent = $client->getRawFile($project, 'composer.json', $project->getDefaultBranch());
        $this->assertStringContainsString('symfony/console', $readmeContent);

        $testCaseContent = $client->getRawFile($project, 'tests/TestCase.php', $project->getDefaultBranch());
        $this->assertStringContainsString('class TestCase', $testCaseContent);
    }
}
