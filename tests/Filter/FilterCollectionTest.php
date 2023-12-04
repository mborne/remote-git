<?php

namespace MBO\RemoteGit\Tests\Filter;

use MBO\RemoteGit\Tests\TestCase;
use Psr\Log\NullLogger;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Filter\FilterCollection;

/**
 * Test FilterCollection
 */
class FilterCollectionTest extends TestCase
{
    public function testEmpty()
    {
        $filterCollection = new FilterCollection(new NullLogger());
        $project = $this->createMockProject('test');
        $this->assertTrue($filterCollection->isAccepted($project));
    }

    /**
     * Create a fake project filter returning true or false
     *
     * @param bool $accepted
     *
     * @return ProjectFilterInterface
     */
    private function createMockFilter($accepted, $description = 'mock')
    {
        $filter = $this->getMockBuilder(ProjectFilterInterface::class)
            ->getMock()
        ;
        $filter->expects($this->any())
            ->method('isAccepted')
            ->willReturn($accepted)
        ;
        $filter->expects($this->any())
            ->method('getDescription')
            ->willReturn($description)
        ;

        return $filter;
    }

    public function testOneTrue()
    {
        $filterCollection = new FilterCollection(new NullLogger());
        $filterCollection->addFilter($this->createMockFilter(true));
        $project = $this->createMockProject('test');
        $this->assertTrue($filterCollection->isAccepted($project));
    }

    public function testOneFalse()
    {
        $filterCollection = new FilterCollection(new NullLogger());
        $filterCollection->addFilter($this->createMockFilter(false));
        $project = $this->createMockProject('test');
        $this->assertFalse($filterCollection->isAccepted($project));
    }

    /**
     * Check that isAccepted is unanymous
     */
    public function testTrueFalseTrue()
    {
        $filterCollection = new FilterCollection(new NullLogger());
        $filterCollection->addFilter($this->createMockFilter(true, 'mock-1'));
        $filterCollection->addFilter($this->createMockFilter(false, 'mock-2'));
        $filterCollection->addFilter($this->createMockFilter(true, 'mock-3'));
        $project = $this->createMockProject('test');
        $this->assertFalse($filterCollection->isAccepted($project));

        $this->assertEquals(
            '- mock-1'.PHP_EOL.'- mock-2'.PHP_EOL.'- mock-3',
            $filterCollection->getDescription()
        );
    }
}
