<?php

namespace MBO\RemoteGit\Tests;

use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\Exception\RawFileNotFoundException;
use MBO\RemoteGit\ProjectInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Create a fake project with a given name.
     */
    protected function createMockProject(string $projectName): ProjectInterface
    {
        $project = $this->createStub(ProjectInterface::class);
        $project->method('getName')->willReturn($projectName);
        $project->method('getDefaultBranch')->willReturn('master');

        return $project;
    }

    protected function getDataPath(string $relativePath): string
    {
        $absolutePath = __DIR__.'/data/'.$relativePath;
        $this->assertFileExists($absolutePath, "Data file '$relativePath' should exists");

        return $absolutePath;
    }

    protected function getDataJson(string $relativePath): array
    {
        $absolutePath = $this->getDataPath($relativePath);
        $content = file_get_contents($absolutePath);
        $this->assertNotFalse($content, "Failed to read data file '$relativePath'");
        $json = json_decode($content, true);
        $this->assertNotNull($json, "Failed to decode JSON from data file '$relativePath'");

        return $json;
    }

    /**
     * Ensure that getter works for project.
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
        ProjectInterface $project,
    ): void {
        $defaultBranch = $project->getDefaultBranch();
        $this->assertNotNull($defaultBranch);

        // try to retrieve missing file
        $thrown = null;
        try {
            $client->getRawFile($project, 'NOT-FOUND.md', $defaultBranch);
        } catch (\Exception $e) {
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
