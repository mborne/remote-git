<?php

namespace MBO\RemoteGit\Tests\Filter;

use MBO\RemoteGit\Tests\TestCase;

use Psr\Log\NullLogger;

use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Filter\RequiredFileFilter;

/**
 * Test RequiredFileFilter
 */
class RequiredFileFilterTest extends TestCase {
    
    /**
     * Rejected if composer.json doesn't exists
     */
    public function testRequiredFileMissing(){
        $project = $this->createMockProject('test');

        $gitClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock()
        ;
        $gitClient
            ->expects($this->once())
            ->method('getRawFile')
            ->willThrowException(new \Exception("404 not found"))
        ;
        $filter = new RequiredFileFilter($gitClient,'README.md');
        $this->assertFalse($filter->isAccepted($project));
    }


    /**
     * Accepted if composer.json exists
     */
    public function testRequiredFilePresent(){
        $project = $this->createMockProject('test');

        $gitClient = $this->getMockBuilder(ClientInterface::class)
            ->getMock()
        ;
        $content = 'readme content';
        $gitClient
            ->expects($this->any())
            ->method('getRawFile')
            //->with(['composer.json'])
            ->willReturn(json_encode($content))
        ;
        $filter = new RequiredFileFilter($gitClient,'README.md');
        $this->assertTrue($filter->isAccepted($project));
    }


}

