<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Upcasting;

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\EventStore\EventStore;
use Ssmiff\CqrsEs\EventStore\Visitor\Criteria;
use Ssmiff\CqrsEs\EventStore\Visitor\EventVisitor;
use Ssmiff\CqrsEs\EventStore\Visitor\VisitsEvents;
use Ssmiff\CqrsEs\DomainEventStream;

final readonly class UpcastingEventStore implements EventStore, VisitsEvents
{
    /**
     * @param EventStore&VisitsEvents $eventStore
     */
    public function __construct(
        private EventStore $eventStore,
        private UpcasterChain $upcasterChain,
    ) {}

    public function retrieve(AggregateRootId $aggregateRootId): DomainEventStream
    {
        return $this->upcastStream(
            $this->eventStore->retrieve($aggregateRootId),
            $aggregateRootId,
        );
    }

    private function upcastStream(DomainEventStream $eventStream, AggregateRootId $aggregateRootId): DomainEventStream
    {
        $upcastedEvents = [];

        foreach ($eventStream as $domainMessage) {
            $upcastedEvents[] = $this->upcasterChain->upcast($domainMessage);
        }

        return new DomainEventStream($upcastedEvents);
    }

    public function retrieveFromVersion(AggregateRootId $aggregateRootId, int $version): DomainEventStream
    {
        return $this->upcastStream(
            $this->eventStore->retrieveFromVersion($aggregateRootId, $version),
            $aggregateRootId,
        );
    }

    public function append(AggregateRootId $aggregateRootId, DomainEventStream $eventStream): void
    {
        $this->eventStore->append($aggregateRootId, $eventStream);
    }

    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor): void
    {
        $this->eventStore->visitEvents($criteria, $eventVisitor);
    }
}
