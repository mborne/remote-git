<?php

namespace MBO\RemoteGit\Tests;

use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\ClientOptions;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\Gogs\GogsClient;
use MBO\RemoteGit\Gogs\GogsProject;
use MBO\RemoteGit\ProjectVisibility;

/**
 * Test GogsClient with gitea.com using the following public projects :
 *
 * - https://gitea.com/mborne/sample-composer
 * - https://gitea.com/docker/metadata-action
 */
class GiteaClientTest extends TestCase
{
    /**
     * Create gogs client for gitea.com using GITEA_TOKEN.
     */
    protected function createGitClient(): GogsClient
    {
        $gitlabToken = getenv('GITEA_TOKEN');
        if (empty($gitlabToken)) {
            $this->markTestSkipped('Missing GITEA_TOKEN for gitea.com');
        }

        $clientOptions = new ClientOptions();
        $clientOptions
            ->setUrl('https://gitea.com')
            ->setToken($gitlabToken)
            ->setType(GogsClient::TYPE)
        ;

        /* create client */
        $client = ClientFactory::createClient(
            $clientOptions
        );
        $this->assertInstanceOf(GogsClient::class, $client);

        return $client;
    }

    /**
     * Test find by current user.
     */
    public function testFindByCurrentUser(): void
    {
        /* create client */
        $client = $this->createGitClient();
        $this->assertInstanceOf(GogsClient::class, $client);

        /* search projects */
        $findOptions = new FindOptions();
        $projects = $client->find($findOptions);

        $this->assertIsArray($projects);
        $this->assertNotEmpty($projects);

        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GogsProject::class, $project);
            $this->assertGettersWorks($project);
            $projectsByName[$project->getName()] = $project;
        }

        $this->assertArrayHasKey(
            'mborne/sample-composer',
            $projectsByName
        );

        /* test getDefaultBranch */
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);

        /* test isArchived */
        $this->assertFalse($project->isArchived());

        /* test getVisibility */
        $this->assertEquals(ProjectVisibility::PUBLIC, $project->getVisibility());

        /* test getRawFile */
        $project = $projectsByName['mborne/sample-composer'];
        $this->assertStringContainsString(
            '# mborne/sample-composer',
            $client->getRawFile($project, 'README.md', 'master')
        );

        /* test getRawFile not found */
        $this->ensureThatRawFileNotFoundThrowsException($client, $project);
    }

    /**
     * Ensure client can find projects by username and organizations.
     */
    public function testFindByUserAndOrgs(): void
    {
        /* create client */
        $client = $this->createGitClient();
        $this->assertInstanceOf(GogsClient::class, $client);

        /* search projects */
        $findOptions = new FindOptions();
        $findOptions->setUsers(['mborne']);
        $findOptions->setOrganizations(['docker']);
        $projects = $client->find($findOptions);

        $this->assertIsArray($projects);
        $this->assertNotEmpty($projects);

        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GogsProject::class, $project);
            $this->assertGettersWorks($project);
            $projectsByName[$project->getName()] = $project;
        }

        /* retrieve sample project */
        $this->assertArrayHasKey(
            'docker/metadata-action',
            $projectsByName
        );
        $project = $projectsByName['docker/metadata-action'];

        /* test getDescription */
        $this->assertStringContainsString(
            'Mirror of https://github.com/docker/metadata-action',
            $project->getDescription()
        );
    }
}
