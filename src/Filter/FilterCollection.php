<?php

namespace MBO\RemoteGit\Filter;

use Psr\Log\LoggerInterface;
use MBO\RemoteGit\ProjectInterface;
use MBO\RemoteGit\ProjectFilterInterface;
use MBO\RemoteGit\Helper\LoggerHelper;

/**
 * Compose a list of filter to simplify command line integration
 *
 * @author mborne
 */
class FilterCollection implements ProjectFilterInterface
{
    /**
     * @var ProjectFilterInterface[]
     */
    private $filters;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->filters = [];
        $this->logger = LoggerHelper::handleNull($logger);
    }

    /**
     * Add a filter to the collection
     */
    public function addFilter(ProjectFilterInterface $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        $parts = [];
        foreach ($this->filters as $filter) {
            $parts[] = '- '.$filter->getDescription();
        }

        return implode(PHP_EOL, $parts);
    }

    /**
     * {@inheritDoc}
     */
    public function isAccepted(ProjectInterface $project): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->isAccepted($project)) {
                $this->logger->info(sprintf(
                    '[%s]Ignoring project %s (%s)',
                    $this->getFilterName($filter),
                    $project->getName(),
                    $filter->getDescription()
                ));

                return false;
            }
        }
        $this->logger->debug(sprintf(
            '[FilterCollection]keep project %s',
            $project->getName()
        ));

        return true;
    }

    /**
     * Get filter name
     */
    private function getFilterName(ProjectFilterInterface $filter): string
    {
        $clazz = get_class($filter);
        $parts = explode('\\', $clazz);

        return $parts[count($parts) - 1];
    }
}
