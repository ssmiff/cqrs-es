<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\EventStore;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Aggregate\UuidAggregateRootId;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;
use Ssmiff\CqrsEs\EventStore\InMemoryEventStore;
use Ssmiff\CqrsEs\EventStore\Visitor\Criteria;
use Ssmiff\CqrsEs\EventStore\Visitor\EventVisitor;
use Ssmiff\CqrsEs\EventStore\Visitor\VisitsEvents;
use Ssmiff\CqrsEs\Metadata;

#[CoversClass(InMemoryEventStore::class)]
#[UsesClass(DomainEvent::class)]
#[UsesClass(DomainEventStream::class)]
#[UsesClass(UuidAggregateRootId::class)]
final class VisitEventsTest extends TestCase
{
    #[Test]
    public function it_visits_events_matching_criteria_in_insertion_order(): void
    {
        $store = new InMemoryEventStore();

        $id1 = UuidAggregateRootId::new();
        $id2 = UuidAggregateRootId::new();

        $stream1 = new DomainEventStream([
            DomainEvent::recordNow($id1, 0, (object)['agg' => 'a', 'v' => 0], new Metadata()),
            DomainEvent::recordNow($id1, 1, (object)['agg' => 'a', 'v' => 1], new Metadata()),
        ]);
        $store->append($id1, $stream1);

        $stream2 = new DomainEventStream([
            DomainEvent::recordNow($id2, 0, (object)['agg' => 'b', 'v' => 0], new Metadata()),
            DomainEvent::recordNow($id2, 1, (object)['agg' => 'b', 'v' => 1], new Metadata()),
            DomainEvent::recordNow($id2, 2, (object)['agg' => 'b', 'v' => 2], new Metadata()),
        ]);
        $store->append($id2, $stream2);

        // match only events with version >= 1
        $criteria = new class implements Criteria {
            public function getAggregateRootIds(): array { return []; }
            public function getAggregateRootIdTypes(): array { return []; }
            public function getEventTypes(): array { return []; }
            public function isMatchedBy(DomainEvent $event): bool { return $event->getVersion() >= 1; }
        };

        $visited = [];
        $visitor = new class($visited) implements EventVisitor {
            public array $visited;
            public function __construct(array &$visited) { $this->visited = &$visited; }
            public function visitEvent(DomainEvent $event): void { $this->visited[] = $event; }
        };

        // ensure InMemoryEventStore implements VisitsEvents
        $this->assertInstanceOf(VisitsEvents::class, $store);

        $store->visitEvents($criteria, $visitor);

        $this->assertCount(3, $visited);
        $this->assertSame([1, 1, 2], array_map(fn(DomainEvent $e) => $e->getVersion(), $visited));
        $this->assertSame(['a', 'b', 'b'], array_map(fn(DomainEvent $e) => $e->getPayload()->agg, $visited));
    }
}
