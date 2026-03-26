<?php

namespace MBO\RemoteGit\Tests\Filter;

use MBO\RemoteGit\ClientInterface;
use MBO\RemoteGit\Filter\RequiredFileFilter;
use MBO\RemoteGit\Tests\TestCase;

/**
 * Test RequiredFileFilter.
 */
class RequiredFileFilterTest extends TestCase
{
    /**
     * Rejected if composer.json doesn't exists.
     */
    public function testRequiredFileMissing(): void
    {
        $project = $this->createMockProject('test');

        $gitClient = $this->createMock(ClientInterface::class);
        $gitClient
            ->expects($this->once())
            ->method('getRawFile')
            ->willThrowException(new \Exception('404 not found'));
        $filter = new RequiredFileFilter($gitClient, 'README.md');
        $this->assertFalse($filter->isAccepted($project));
    }

    /**
     * Accepted if composer.json exists.
     */
    public function testRequiredFilePresent(): void
    {
        $project = $this->createMockProject('test');

        $gitClient = $this->createStub(ClientInterface::class);
        $content = 'readme content';
        $gitClient->method('getRawFile')->willReturn(json_encode($content));
        $filter = new RequiredFileFilter($gitClient, 'README.md');
        $this->assertTrue($filter->isAccepted($project));
    }
}
