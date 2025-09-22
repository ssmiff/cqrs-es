<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore;

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;

final class TraceableEventStore implements EventStore
{
    private array $recordedEvents = [];

    private bool $isTracing = false;

    public function __construct(private readonly EventStore $eventStore) {}

    public function retrieve(AggregateRootId $aggregateRootId): DomainEventStream
    {
        return $this->eventStore->retrieve($aggregateRootId);
    }

    public function retrieveFromVersion(AggregateRootId $aggregateRootId, int $version): DomainEventStream
    {
        return $this->eventStore->retrieveFromVersion($aggregateRootId, $version);
    }

    public function append(AggregateRootId $aggregateRootId, DomainEventStream $eventStream): void
    {
        $this->eventStore->append($aggregateRootId, $eventStream);

        if (!$this->isTracing) {
            return;
        }

        foreach ($eventStream as $event) {
            $this->recordedEvents[] = $event;
        }
    }

    /**
     * @return DomainEvent[]
     */
    public function getEvents(): array
    {
        return array_map(
            function (DomainEvent $event) {
                return $event->getPayload();
            },
            $this->recordedEvents
        );
    }

    public function startTracing(): void
    {
        $this->isTracing = true;
    }

    /**
     * Clear any previously recorded events.
     */
    public function clearEvents(): void
    {
        $this->recordedEvents = [];
    }
}
