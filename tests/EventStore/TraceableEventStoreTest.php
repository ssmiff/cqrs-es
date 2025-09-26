<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\EventStore;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Aggregate\UuidAggregateRootId;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;
use Ssmiff\CqrsEs\EventStore\InMemoryEventStore;
use Ssmiff\CqrsEs\EventStore\TraceableEventStore;
use Ssmiff\CqrsEs\Metadata;

#[CoversClass(TraceableEventStore::class)]
final class TraceableEventStoreTest extends TestCase
{
    private InMemoryEventStore $inner;
    private TraceableEventStore $traceable;

    protected function setUp(): void
    {
        $this->inner = new InMemoryEventStore();
        $this->traceable = new TraceableEventStore($this->inner);
    }

    #[Test]
    public function it_does_not_record_when_not_tracing(): void
    {
        $id = UuidAggregateRootId::new();

        $this->traceable->append($id, new DomainEventStream([
            DomainEvent::recordNow($id, 0, (object)['a' => 1], new Metadata()),
        ]));

        $this->assertSame([], $this->traceable->getEvents());
    }

    #[Test]
    public function it_records_payloads_when_tracing(): void
    {
        $id = UuidAggregateRootId::new();

        $this->traceable->startTracing();

        $payload1 = (object)['type' => 'one'];
        $payload2 = (object)['type' => 'two'];

        $this->traceable->append($id, new DomainEventStream([
            DomainEvent::recordNow($id, 0, $payload1, new Metadata()),
            DomainEvent::recordNow($id, 1, $payload2, new Metadata()),
        ]));

        $events = $this->traceable->getEvents();

        $this->assertCount(2, $events);
        $this->assertEquals($payload1, $events[0]);
        $this->assertEquals($payload2, $events[1]);
    }

    #[Test]
    public function it_can_clear_recorded_events(): void
    {
        $id = UuidAggregateRootId::new();
        $this->traceable->startTracing();

        $this->traceable->append($id, new DomainEventStream([
            DomainEvent::recordNow($id, 0, (object)['a' => 1], new Metadata()),
        ]));

        $this->assertCount(1, $this->traceable->getEvents());

        $this->traceable->clearEvents();

        $this->assertSame([], $this->traceable->getEvents());
    }

    #[Test]
    public function it_proxies_retrieve_and_append_to_inner_store(): void
    {
        $id = UuidAggregateRootId::new();

        $stream = new DomainEventStream([
            DomainEvent::recordNow($id, 0, (object)['x' => 1], new Metadata()),
            DomainEvent::recordNow($id, 1, (object)['x' => 2], new Metadata()),
        ]);

        $this->traceable->append($id, $stream);

        $retrieved = $this->traceable->retrieve($id);

        // The inner store should contain the same stream and retrieve should proxy.
        $this->assertEquals($stream, $retrieved);

        // And retrieveFromVersion should proxy as well.
        $from1 = $this->traceable->retrieveFromVersion($id, 1);
        $this->assertCount(1, $from1);
    }
}
