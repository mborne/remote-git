<?php

namespace MBO\RemoteGit\Tests;

use MBO\RemoteGit\ClientOptions;
use MBO\RemoteGit\ClientFactory;
use MBO\RemoteGit\FindOptions;
use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\Gogs\GogsClient;
use MBO\RemoteGit\Gogs\GogsProject;

class GogsClientTest extends TestCase
{
    /**
     * @return ClientInterface
     */
    protected function createGitClient()
    {
        $gitlabToken = getenv('SATIS_GOGS_TOKEN');
        if (empty($gitlabToken)) {
            $this->markTestSkipped('Missing SATIS_GOGS_TOKEN for gogs.quadtreeworld.net');
        }

        $clientOptions = new ClientOptions();
        $clientOptions
            ->setUrl('https://gogs.quadtreeworld.net')
            ->setToken($gitlabToken)
        ;

        /* create client */
        return ClientFactory::createClient(
            $clientOptions
        );
    }

    /**
     * Test find by current user
     */
    public function testFindByCurrentUser()
    {
        /* create client */
        $client = $this->createGitClient();
        $this->assertInstanceOf(GogsClient::class, $client);

        /* search projects */
        $findOptions = new FindOptions();
        $projects = $client->find($findOptions);

        $this->assertTrue(is_array($projects));
        $this->assertNotEmpty($projects);

        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GogsProject::class, $project);
            $this->assertGettersWorks($project);
            $projectsByName[$project->getName()] = $project;
        }

        $this->assertArrayHasKey(
            'docker/docker-php',
            $projectsByName
        );
        $project = $projectsByName['docker/docker-php'];
        $this->assertContains(
            'FROM ',
            $client->getRawFile($project, 'Dockerfile', 'master')
        );
        $this->assertContains(
            'ServerTokens Prod',
            $client->getRawFile($project, 'conf/apache-security.conf', 'master')
        );
    }

    /**
     * Ensure client can find projects by username and organizations
     */
    public function testFindByUserAndOrgs()
    {
        /* create client */
        $client = $this->createGitClient();
        $this->assertInstanceOf(GogsClient::class, $client);

        /* search projects */
        $findOptions = new FindOptions();
        $findOptions->setUsers(['mborne']);
        $findOptions->setOrganizations(['docker']);
        $projects = $client->find($findOptions);

        $this->assertTrue(is_array($projects));
        $this->assertNotEmpty($projects);

        $projectsByName = [];
        foreach ($projects as $project) {
            $this->assertInstanceOf(GogsProject::class, $project);
            $this->assertGettersWorks($project);
            $projectsByName[$project->getName()] = $project;
        }

        $this->assertArrayHasKey(
            'docker/docker-php',
            $projectsByName
        );
        $project = $projectsByName['docker/docker-php'];
        $this->assertContains(
            'FROM ',
            $client->getRawFile($project, 'Dockerfile', 'master')
        );
        $this->assertContains(
            'ServerTokens Prod',
            $client->getRawFile($project, 'conf/apache-security.conf', 'master')
        );
    }
}
