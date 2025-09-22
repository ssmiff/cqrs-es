<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing\AggregateFactory;

use Ssmiff\CqrsEs\EventSourcing\EventSourcedAggregateRoot;
use Ssmiff\CqrsEs\DomainEventStream;

interface AggregateFactory
{
    /**
     * @param class-string<EventSourcedAggregateRoot> $aggregateClass
     */
    public function create(string $aggregateClass, DomainEventStream $eventStream): EventSourcedAggregateRoot;
}
