<?php

namespace MBO\RemoteGit\Tests\Filter;

use MBO\RemoteGit\Filter\IncludeRegexpFilter;
use MBO\RemoteGit\Tests\TestCase;

/**
 * Test IncludeRegexpFilter.
 */
class IncludeRegexpFilterTest extends TestCase
{
    public function testExample(): void
    {
        $filter = new IncludeRegexpFilter('(^mborne|cours\-|tp\-)');

        $this->assertEquals(
            'project name should match /(^mborne|cours\-|tp\-)/',
            $filter->getDescription()
        );

        $expectedResults = [
            'mborne/git-manager' => true,
            'somebody/cours-patron-conception' => true,
            'somebody/tp-patron-conception' => true,
            'meuh' => false,
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
