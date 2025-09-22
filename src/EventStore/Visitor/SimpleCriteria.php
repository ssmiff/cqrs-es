<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Visitor;

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\DomainEvent;

final class SimpleCriteria implements Criteria
{
    /** @var AggregateRootId[] */
    private array $aggregateRootIds = [];

    /** @var string[] */
    private array $aggregateRootIdTypes = [];

    /** @var string[] */
    private array $eventTypes = [];

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param AggregateRootId[] $aggregateRootIds
     */
    public function withAggregateRootIds(array $aggregateRootIds): self
    {
        $instance = clone $this;
        $instance->aggregateRootIds = $aggregateRootIds;

        return $instance;
    }

    /**
     * @param string[] $aggregateRootIdTypes
     */
    public function withAggregateRootIdTypes(array $aggregateRootIdTypes): self
    {
        $instance = clone $this;
        $instance->aggregateRootIdTypes = $aggregateRootIdTypes;

        return $instance;
    }

    /**
     * @param string[] $eventTypes
     */
    public function withEventTypes(array $eventTypes): self
    {
        $instance = clone $this;
        $instance->eventTypes = $eventTypes;

        return $instance;
    }

    public function getAggregateRootIds(): array
    {
        return $this->aggregateRootIds;
    }

    public function getAggregateRootIdTypes(): array
    {
        return $this->aggregateRootIdTypes;
    }

    public function getEventTypes(): array
    {
        return $this->eventTypes;
    }

    public function isMatchedBy(DomainEvent $event): bool
    {
        throw new \Exception('Not implemented');
    }
}
