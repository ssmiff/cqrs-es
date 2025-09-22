<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore;

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\EventStore\Exception\DuplicateVersionException;
use Ssmiff\CqrsEs\EventStore\Exception\EventStreamNotFoundException;
use Ssmiff\CqrsEs\EventStore\Visitor\Criteria;
use Ssmiff\CqrsEs\EventStore\Visitor\EventVisitor;
use Ssmiff\CqrsEs\EventStore\Visitor\VisitsEvents;
use Ssmiff\CqrsEs\DomainEventStream;

final class InMemoryEventStore implements EventStore, VisitsEvents
{
    /**
     * @var array{int, array<DomainEvent>}
     */
    private array $events = [];

    /**
     * @throws EventStreamNotFoundException
     */
    public function retrieve(AggregateRootId $aggregateRootId): DomainEventStream
    {
        $id = (string)$aggregateRootId;

        if (!isset($this->events[$id])) {
            throw EventStreamNotFoundException::withAggregateId($aggregateRootId);
        }

        return new DomainEventStream($this->events[$id]);
    }

    public function retrieveFromVersion(AggregateRootId $aggregateRootId, int $version): DomainEventStream
    {
        $id = (string)$aggregateRootId;

        if (!isset($this->events[$id])) {
            return new DomainEventStream([]);
        }

        return new DomainEventStream(
            array_values(
                array_filter(
                    $this->events[$id],
                    fn (DomainEvent $event) => $version <= $event->getVersion(),
                )
            )
        );
    }

    /**
     * @throws DuplicateVersionException
     */
    public function append(AggregateRootId $aggregateRootId, DomainEventStream $eventStream): void
    {
        $id = (string)$aggregateRootId;

        if (!isset($this->events[$id])) {
            $this->events[$id] = [];
        }

        $this->assertStream($this->events[$id], $eventStream);

        /** @var DomainEvent $event */
        foreach ($eventStream as $event) {
            $version = $event->getVersion();

            $this->events[$id][$version] = $event;
        }
    }

    /**
     * @param DomainEvent[] $events
     */
    private function assertStream(array $events, DomainEventStream $eventsToAppend): void
    {
        /** @var DomainEvent $event */
        foreach ($eventsToAppend as $event) {
            $version = $event->getVersion();

            if (isset($events[$version])) {
                throw DuplicateVersionException::forEventStream($eventsToAppend);
            }
        }
    }

    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor): void
    {
        foreach ($this->events as $events) {
            foreach ($events as $event) {
                if (!$criteria->isMatchedBy($event)) {
                    continue;
                }

                $eventVisitor->visitEvent($event);
            }
        }
    }
}
