<?php

namespace MBO\RemoteGit\Tests;

use Exception;
use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\Exception\RawFileNotFoundException;
use PHPUnit\Framework\TestCase as BaseTestCase;
use MBO\RemoteGit\ProjectInterface;

class TestCase extends BaseTestCase
{
    /**
     * Create a fake project with a given name
     *
     * @return ProjectInterface
     */
    protected function createMockProject(string $projectName)
    {
        $project = $this->getMockBuilder(ProjectInterface::class)
            ->getMock()
        ;
        $project->expects($this->any())
            ->method('getName')
            ->willReturn($projectName)
        ;
        $project->expects($this->any())
            ->method('getDefaultBranch')
            ->willReturn('master')
        ;

        return $project;
    }

    /**
     * Ensure that getter works for project
     */
    protected function assertGettersWorks(ProjectInterface $project): void
    {
        $this->assertNotEmpty($project->getId());
        $this->assertNotEmpty($project->getName());
        // should not crash (can be null or empty)
        $project->getDefaultBranch();
        $this->assertNotEmpty($project->getHttpUrl());
        $this->assertNotEmpty($project->getRawMetadata());
    }

    /**
     * Try to access NOT-FOUND.md on default branch and check RawFileNotFoundException.
     */
    protected function ensureThatRawFileNotFoundThrowsException(
        ClientInterface $client,
        ProjectInterface $project
    ): void {
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);

        // try to retrieve missing file
        $thrown = null;
        try {
            $client->getRawFile($project, 'NOT-FOUND.md', $defaultBranch);
        } catch (Exception $e) {
            $thrown = $e;
        }
        $this->assertNotNull($thrown);
        $this->assertInstanceOf(RawFileNotFoundException::class, $thrown);
        $this->assertEquals(
            "file 'NOT-FOUND.md' not found on branch '".$defaultBranch."'",
            $thrown->getMessage()
        );
    }
}
