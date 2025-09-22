<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore;

use DateTimeInterface;
use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\Clock\FrozenClock;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\EventStore\Exception\DuplicateVersionException;
use Ssmiff\CqrsEs\EventStore\Exception\EventStreamNotFoundException;
use Ssmiff\CqrsEs\EventStore\Visitor\Criteria;
use Ssmiff\CqrsEs\EventStore\Visitor\EventVisitor;
use Ssmiff\CqrsEs\EventStore\Visitor\VisitsEvents;
use Ssmiff\CqrsEs\DomainEventStream;
use Ssmiff\CqrsEs\Metadata;
use Ssmiff\CqrsEs\Serializer\Inflector\ClassNameInflector;
use Ssmiff\CqrsEs\Serializer\Serializer;

final class SerializedMemoryEventStore implements EventStore, VisitsEvents
{
    public function __construct(
        private readonly Serializer $payloadSerializer,
        private readonly Serializer $metadataSerializer,
        private readonly ClassNameInflector $classNameInflector,
    ) {}

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

        $events = array_map(
            fn(array $serializedEvent) => $this->deserializeEvent($serializedEvent),
            $this->events[$id],
        );

        return new DomainEventStream($events);
    }

    public function retrieveFromVersion(AggregateRootId $aggregateRootId, int $version): DomainEventStream
    {
        $id = (string)$aggregateRootId;

        if (!isset($this->events[$id])) {
            return new DomainEventStream([]);
        }

        $events = array_values(
            array_filter(
                array_map(
                    fn(array $serializedEvent) => $this->deserializeEvent($serializedEvent),
                    $this->events[$id],
                ),
                fn(DomainEvent $event) => $version <= $event->getVersion(),
            ),
        );

        return new DomainEventStream($events);
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

            $this->events[$id][$version] = $this->serializeEvent($event);
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

    private function serializeEvent(DomainEvent $event): array
    {
        return [
            'aggregateId' => [
                'type' => $this->classNameInflector->instanceToType($event->getAggregateId()),
                'id' => (string)$event->getAggregateId(),
            ],
            'version' => $event->getVersion(),
            'payload' => $this->payloadSerializer->serialize($event->getPayload()),
            'metadata' => $this->metadataSerializer->serialize($event->getMetaData()),
            'recordedAt' => $event->getRecordedOn()->now()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function deserializeEvent(array $serializedEvent): DomainEvent
    {
        /** @var class-string<AggregateRootId> $aggregateRootIdClass */
        $aggregateRootIdClass = $this->classNameInflector->typeToClassName($serializedEvent['aggregateId']['type']);

        /** @var AggregateRootId $aggregateRootId */
        $aggregateRootId = $aggregateRootIdClass::fromString($serializedEvent['aggregateId']['id']);

        $payload = $this->payloadSerializer->deserialize($serializedEvent['payload']);

        /** @var Metadata $metadata */
        $metadata = $this->metadataSerializer->deserialize($serializedEvent['metadata']);

        return new DomainEvent(
            $aggregateRootId,
            (int)$serializedEvent['version'],
            FrozenClock::fromFormattedString(DateTimeInterface::ATOM, $serializedEvent['recordedAt']),
            $payload,
            $metadata,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
