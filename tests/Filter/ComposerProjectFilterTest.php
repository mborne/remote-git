<?php

namespace MBO\RemoteGit\Tests\Filter;

use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\Filter\ComposerProjectFilter;
use MBO\RemoteGit\Tests\TestCase;

/**
 * Test ComposerProjectFilter.
 */
class ComposerProjectFilterTest extends TestCase
{
    /**
     * Test getDescription.
     */
    public function testGetDescription(): void
    {
        $gitClient = $this->createStub(ClientInterface::class);
        $filter = new ComposerProjectFilter($gitClient);
        $this->assertEquals(
            'composer.json should exists',
            $filter->getDescription()
        );
        $filter->setProjectType('library');
        $this->assertEquals(
            "composer.json should exists and type should be 'library'",
            $filter->getDescription()
        );
    }

    /**
     * Rejected if composer.json doesn't exists.
     */
    public function testMissingComposerJson(): void
    {
        $project = $this->createMockProject('test');

        $gitClient = $this->createMock(ClientInterface::class);
        $gitClient
            ->expects($this->once())
            ->method('getRawFile')
            ->willThrowException(new \Exception('404 not found'));
        $filter = new ComposerProjectFilter($gitClient);
        $this->assertFalse($filter->isAccepted($project));
    }

    /**
     * Accepted if composer.json exists.
     */
    public function testComposerJsonAndTypeFilter(): void
    {
        $project = $this->createMockProject('test');

        $gitClient = $this->createMock(ClientInterface::class);
        $content = [
            'name' => 'something',
            'type' => 'project',
        ];
        $gitClient
            ->expects($this->exactly(3))
            ->method('getRawFile')
            ->willReturn(json_encode($content));
        $filter = new ComposerProjectFilter($gitClient);
        $this->assertTrue($filter->isAccepted($project));

        // filter with type=project
        $filter->setProjectType('project');
        $this->assertTrue($filter->isAccepted($project));

        // filter with type=library
        $filter->setProjectType('library');
        $this->assertFalse($filter->isAccepted($project));
    }

    /**
     * Accepted if composer.json exists.
     */
    public function testComposerJsonAndMultipleTypeFilter(): void
    {
        $project = $this->createMockProject('test');

        $gitClient = $this->createStub(ClientInterface::class);
        $content = [
            'name' => 'something',
            'type' => 'library',
        ];
        $gitClient->method('getRawFile')->willReturn(json_encode($content));
        $filter = new ComposerProjectFilter($gitClient);
        $this->assertTrue($filter->isAccepted($project));
        $filter->setProjectType('project,library');
        $this->assertTrue($filter->isAccepted($project));
    }
}
