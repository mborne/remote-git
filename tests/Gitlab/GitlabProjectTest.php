<?php

namespace MBO\RemoteGit\Tests\Gitlab;

use MBO\RemoteGit\Gitlab\GitlabProject;
use MBO\RemoteGit\ProjectVisibility;
use MBO\RemoteGit\Tests\TestCase;

class GitlabProjectTest extends TestCase
{
    private GitlabProject $project;

    protected function setUp(): void
    {
        $this->project = new GitlabProject($this->getDataJson('gitlab/sample-composer.json'));
    }

    public function testGetId(): void
    {
        $this->assertEquals('7406180', $this->project->getId());
    }

    public function testGetName(): void
    {
        $this->assertEquals('mborne/sample-composer', $this->project->getName());
    }

    public function testGetDescription(): void
    {
        $this->assertEquals(
            'This repository provides a project to test [mborne/remote-git](https://github.com/mborne/remote-git)',
            $this->project->getDescription()
        );
    }

    public function testGetTopics(): void
    {
        $this->assertEquals([
            'git-client',
            'testing',
        ], $this->project->getTopics());
    }

    public function testGetDefaultBranch(): void
    {
        $this->assertEquals('master', $this->project->getDefaultBranch());
    }

    public function testGetHttpUrl(): void
    {
        $this->assertEquals(
            'https://gitlab.com/mborne/sample-composer.git',
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

    public function testIsEmpty(): void
    {
        $this->assertFalse($this->project->isEmpty());
    }

    public function testIsEmptyWithEmptyRepo(): void
    {
        $project = new GitlabProject([
            'id' => '12345',
            'empty_repo' => true,
        ]);
        $this->assertTrue($project->isEmpty());
    }

    public function testIsEmptyUndefined(): void
    {
        $project = new GitlabProject([
            'id' => '12345',
        ]);
        $this->assertFalse($project->isEmpty());
    }

    public function testGetRawMetadata(): void
    {
        $metadata = $this->project->getRawMetadata();
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('id', $metadata);
        $this->assertArrayHasKey('path_with_namespace', $metadata);
    }
}
