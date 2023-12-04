<?php

namespace MBO\RemoteGit\Tests\Filter;

use MBO\RemoteGit\Tests\TestCase;
use MBO\RemoteGit\Filter\IgnoreRegexpFilter;

/**
 * Test IgnoreRegexpFilter
 */
class IgnoreRegexpFilterTest extends TestCase
{
    public function testExample()
    {
        $filter = new IgnoreRegexpFilter('(^phpstorm|^typo3\/library)');

        $this->assertEquals(
            'project name should not match /(^phpstorm|^typo3\/library)/',
            $filter->getDescription()
        );

        $expectedResults = [
            'mborne/sample-project' => true,
            'something' => true,
            'meuh' => true,
            'phpstorm/something' => false,
        ];

        foreach ($expectedResults as $projectName => $expected) {
            $project = $this->createMockProject($projectName);
            $this->assertTrue(
                $filter->isAccepted($project) === $expected,
                'unexpected result for '.$projectName
            );
        }
    }
}
