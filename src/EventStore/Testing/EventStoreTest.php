<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Testing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\Aggregate\UuidAggregateRootId;
use Ssmiff\CqrsEs\Clock\FrozenClock;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;
use Ssmiff\CqrsEs\EventStore\EventStore;
use Ssmiff\CqrsEs\EventStore\Exception\DuplicateVersionException;
use Ssmiff\CqrsEs\EventStore\Exception\EventStreamNotFoundException;
use Ssmiff\CqrsEs\Metadata;

abstract class EventStoreTest extends TestCase
{
    protected EventStore $eventStore;

    #[Test]
    public function it_creates_a_new_entry_when_id_is_new(): void
    {
        $id = UuidAggregateRootId::new();

        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 0),
            $this->createDomainMessage($id, 1),
            $this->createDomainMessage($id, 2),
            $this->createDomainMessage($id, 3),
        ]);

        $this->eventStore->append($id, $domainEventStream);

        $this->assertEquals($domainEventStream, $this->eventStore->retrieve($id));
    }

    #[Test]
    public function it_appends_to_an_already_existing_stream(): void
    {
        $id = UuidAggregateRootId::new();
        $dateTime = FrozenClock::fromFormattedString('Y-m-dTH:i:s', '2014-03-12T14:17:19');

        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
        ]);

        $this->eventStore->append($id, $domainEventStream);

        $appendedEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),
        ]);

        $this->eventStore->append($id, $appendedEventStream);

        $expected = new DomainEventStream([
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
            $this->createDomainMessage($id, 4, $dateTime),
            $this->createDomainMessage($id, 5, $dateTime),
        ]);

        $this->assertEquals($expected, $this->eventStore->retrieve($id));
    }

    #[Test]
    public function it_throws_an_exception_when_requesting_the_stream_of_a_non_existing_aggregate(): void
    {
        $this->expectException(EventStreamNotFoundException::class);

        $this->eventStore->retrieve(UuidAggregateRootId::new());
    }

    #[Test]
    public function it_throws_an_exception_when_appending_a_duplicate_version(): void
    {
        $id = UuidAggregateRootId::new();

        $eventStream = new DomainEventStream([$this->createDomainMessage($id, 0)]);

        $this->expectException(DuplicateVersionException::class);

        $this->eventStore->append($id, $eventStream);
        $this->eventStore->append($id, $eventStream);
    }

    #[Test]
    public function it_loads_events_starting_from_a_given_playhead(): void
    {
        $id = UuidAggregateRootId::new();

        $dateTime = FrozenClock::fromFormattedString('Y-m-dTH:i:s', '2014-03-12T14:17:19');

        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($id, 0, $dateTime),
            $this->createDomainMessage($id, 1, $dateTime),
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
        ]);

        $this->eventStore->append($id, $domainEventStream);

        $expected = new DomainEventStream([
            $this->createDomainMessage($id, 2, $dateTime),
            $this->createDomainMessage($id, 3, $dateTime),
        ]);

        $this->assertEquals($expected, $this->eventStore->retrieveFromVersion($id, 2));
    }

    #[Test]
    public function empty_set_of_events_can_be_added(): void
    {
        $id = UuidAggregateRootId::new();

        $domainMessage = $this->createDomainMessage(1, 0);
        $baseStream = new DomainEventStream([$domainMessage]);

        $this->eventStore->append($id, $baseStream);

        $appendedEventStream = new DomainEventStream([]);

        $this->eventStore->append($id, $appendedEventStream);

        $events = $this->eventStore->retrieve($id);

        $this->assertCount(1, $events);
    }

    #[Test]
    public function it_returns_empty_event_stream_when_no_events_are_committed_since_given_version(): void
    {
        $id = UuidAggregateRootId::new();

        $this->eventStore->append($id, new DomainEventStream([
            $this->createDomainMessage($id, 0),
        ]));

        $this->assertEquals(
            new DomainEventStream([]),
            $this->eventStore->retrieveFromVersion($id, 1),
        );
    }

    protected function createDomainMessage(
        AggregateRootId $id,
        int $version,
        ?ClockInterface $recordedOn = null
    ): DomainEvent {
        return new DomainEvent(
            $id,
            $version,
            $recordedOn ??= FrozenClock::fromUTC(),
            (object)[],
            new Metadata(),
        );
    }
}
