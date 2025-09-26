<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Aggregate\UuidAggregateRootId;
use Ssmiff\CqrsEs\Clock\FrozenClock;
use Ssmiff\CqrsEs\Clock\SystemClock;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\Metadata;

#[CoversClass(DomainEvent::class)]
#[UsesClass(Metadata::class)]
final class DomainEventTest extends TestCase
{
    protected function tearDown(): void
    {
        // reset any test clock overrides to avoid cross-test contamination
        DomainEvent::setTestNow(SystemClock::fromSystemTimezone());
    }

    #[Test]
    public function recordNow_uses_provided_test_clock_when_set(): void
    {
        $id = UuidAggregateRootId::new();
        $frozen = FrozenClock::fromFormattedString('Y-m-d H:i:s', '2020-01-01 12:34:56');
        DomainEvent::setTestNow($frozen);

        $event = DomainEvent::recordNow($id, 1, (object)['p' => 1]);

        $this->assertSame(1, $event->getVersion());
        $this->assertSame((string)$id, (string)$event->getAggregateId());
        $this->assertSame('2020-01-01 12:34:56', $event->getRecordedOn()->now()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function recordNow_defaults_to_system_clock_when_not_overridden(): void
    {
        $id = UuidAggregateRootId::new();

        $event = DomainEvent::recordNow($id, 0, (object)['x' => 2]);

        // We cannot rely on exact time, but ensure a ClockInterface is present
        $this->assertInstanceOf(SystemClock::class, $event->getRecordedOn());
    }

    #[Test]
    public function withSingleMeta_returns_new_instance_with_merged_metadata(): void
    {
        $id = UuidAggregateRootId::new();
        $frozen = FrozenClock::fromFormattedString('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $base = new DomainEvent($id, 0, $frozen, (object)[], new Metadata(['a' => 1]));

        $updated = $base->withSingleMeta('b', 2);

        $this->assertNotSame($base, $updated);
        $this->assertSame(['a' => 1], $base->getMetaData()->all());
        $this->assertSame(['a' => 1, 'b' => 2], $updated->getMetaData()->all());
    }

    #[Test]
    public function withMeta_merges_new_metadata_and_returns_new_instance(): void
    {
        $id = UuidAggregateRootId::new();
        $frozen = FrozenClock::fromFormattedString('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $base = new DomainEvent($id, 0, $frozen, (object)[], new Metadata(['a' => 1]));

        $updated = $base->withMeta(new Metadata(['b' => 2, 'a' => 9]));

        $this->assertNotSame($base, $updated);
        // original remains unchanged
        $this->assertSame(['a' => 1], $base->getMetaData()->all());
        // merge should replace existing keys with new values and keep others
        $this->assertSame(['a' => 9, 'b' => 2], $updated->getMetaData()->all());
    }
}
