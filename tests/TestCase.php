<?php

namespace MBO\RemoteGit\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use MBO\RemoteGit\ProjectInterface;

class TestCase extends BaseTestCase
{
    /**
     * Create a fake project with a given name
     *
     * @param string $projectName
     *
     * @return ProjectInterface
     */
    protected function createMockProject($projectName)
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
    protected function assertGettersWorks(ProjectInterface $project)
    {
        $this->assertNotEmpty($project->getId());
        $this->assertNotEmpty($project->getName());
        // should not crash (can be null or empty)
        $project->getDefaultBranch();
        $this->assertNotEmpty($project->getHttpUrl());
        $this->assertNotEmpty($project->getRawMetadata());
    }
}
