<?php

namespace MBO\RemoteGit\Tests\Github;

use MBO\RemoteGit\Github\GithubProject;
use MBO\RemoteGit\ProjectVisibility;
use MBO\RemoteGit\Tests\TestCase;

class GithubProjectTest extends TestCase
{
    private GithubProject $project;

    protected function setUp(): void
    {
        $this->project = new GithubProject($this->getDataJson('github/docker-devbox.json'));
    }

    public function testGetId(): void
    {
        $this->assertEquals('137809011', $this->project->getId());
    }

    public function testGetName(): void
    {
        $this->assertEquals('mborne/docker-devbox', $this->project->getName());
    }

    public function testGetDescription(): void
    {
        $this->assertEquals(
            '[DEV] Docker stacks to quickly setup a dev environment and test some tools.',
            $this->project->getDescription()
        );
    }

    public function testGetDefaultBranch(): void
    {
        $this->assertEquals('master', $this->project->getDefaultBranch());
    }

    public function testGetHttpUrl(): void
    {
        $this->assertEquals(
            'https://github.com/mborne/docker-devbox.git',
            $this->project->getHttpUrl()
        );
    }

    public function testIsArchived(): void
    {
        $this->assertFalse($this->project->isArchived());
    }

    public function testGetVisibilityPublic(): void
    {
        $this->assertEquals(ProjectVisibility::PUBLIC, $this->project->getVisibility());
    }

    public function testGetRawMetadata(): void
    {
        $metadata = $this->project->getRawMetadata();
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('id', $metadata);
        $this->assertArrayHasKey('full_name', $metadata);
    }
}
