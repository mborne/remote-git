<?php

namespace MBO\RemoteGit\Tests\Gogs;

use MBO\RemoteGit\Gogs\GogsProject;
use MBO\RemoteGit\ProjectVisibility;
use MBO\RemoteGit\Tests\TestCase;

class GiteaProjectTest extends TestCase
{
    private GogsProject $project;

    protected function setUp(): void
    {
        $this->project = new GogsProject($this->getDataJson('gitea/sample-composer.json'));
    }

    public function testGetId(): void
    {
        $this->assertEquals('97861', $this->project->getId());
    }

    public function testGetName(): void
    {
        $this->assertEquals('mborne/sample-composer', $this->project->getName());
    }

    public function testGetDescription(): void
    {
        $this->assertEquals(
            'This repository provides a project to test mborne/remote-git',
            $this->project->getDescription()
        );
    }

    public function testGetTopics(): void
    {
        $this->assertEquals([
            'git-client',
            'testing',
            'gitea',
        ], $this->project->getTopics());
    }

    public function testGetDefaultBranch(): void
    {
        $this->assertEquals('master', $this->project->getDefaultBranch());
    }

    public function testGetHttpUrl(): void
    {
        $this->assertEquals(
            'https://gitea.com/mborne/sample-composer.git',
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
