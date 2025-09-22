<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing\AggregateFactory;

use Ssmiff\CqrsEs\EventSourcing\EventSourcedAggregateRoot;
use Ssmiff\CqrsEs\DomainEventStream;

class PublicConstructorAggregateFactory implements AggregateFactory
{
    /**
     * @param class-string<EventSourcedAggregateRoot> $aggregateClass
     */
    public function create(string $aggregateClass, DomainEventStream $eventStream): EventSourcedAggregateRoot
    {
        $aggregate = new $aggregateClass();
        $aggregate->reconstituteFromEventStream($eventStream);
        return $aggregate;
    }
}
