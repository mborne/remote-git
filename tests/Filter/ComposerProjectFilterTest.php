<?php

namespace MBO\RemoteGit\Tests\Filter;

use MBO\RemoteGit\Tests\TestCase;

use Psr\Log\NullLogger;

use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Filter\ComposerProjectFilter;

/**
 * Test ComposerProjectFilter
 */
class ComposerProjectFilterTest extends TestCase {
    
    /**
     * Test getDescription
     */
    public function testGetDescription(){
        $gitClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock()
        ;
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
     * Rejected if composer.json doesn't exists
     */
    public function testMissingComposerJson(){
        $project = $this->createMockProject('test');

        $gitClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock()
        ;
        $gitClient
            ->expects($this->once())
            ->method('getRawFile')
            ->willThrowException(new \Exception("404 not found"))
        ;
        $filter = new ComposerProjectFilter($gitClient);
        $this->assertFalse($filter->isAccepted($project));
    }


    /**
     * Accepted if composer.json exists
     */
    public function testComposerJsonAndTypeFilter(){
        $project = $this->createMockProject('test');

        $gitClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock()
        ;
        $content = array(
            'name' => 'something',
            'type' => 'project'
        );
        $gitClient
            ->expects($this->any())
            ->method('getRawFile')
            //->with(['composer.json'])
            ->willReturn(json_encode($content))
        ;
        $filter = new ComposerProjectFilter($gitClient);
        $this->assertTrue($filter->isAccepted($project));

        // filter with type=project
        $filter->setProjectType('project');
        $this->assertTrue($filter->isAccepted($project));

        // filter with type=library
        $filter->setProjectType('library');
        $this->assertFalse($filter->isAccepted($project));
    }


}

