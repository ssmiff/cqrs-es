<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Aggregate;

use DateTimeInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\FeatureSet;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Ssmiff\CqrsEs\Aggregate\UuidAggregateRootId;
use Ramsey\Uuid\Uuid;

#[CoversClass(UuidAggregateRootId::class)]
class UuidAggregateRootIdTest extends TestCase
{
    #[Test]
    public function new_returns_expected_uuid_aggregate_root_id(): void
    {
        $testUuid = Uuid::uuid7();

        $uuidFactory = new class($testUuid) extends UuidFactory {
            public function __construct(private readonly UuidInterface $testUuid)
            {
                parent::__construct(null);
            }

            public function uuid7(DateTimeInterface|null $dateTime = null): UuidInterface
            {
                return $this->testUuid;
            }
        };

        $originalFactory = Uuid::getFactory();
        Uuid::setFactory($uuidFactory);
        $aggregateRootId = UuidAggregateRootId::new();
        Uuid::setFactory($originalFactory);

        $this->assertInstanceOf(UuidAggregateRootId::class, $aggregateRootId);
        $this->assertSame((string) $testUuid, (string) $aggregateRootId);
    }

    #[Test]
    public function from_string_and_to_string_return_the_same(): void
    {
        $id = 'a1b2c3d4-e5f6-7890-1234-567890abcdef';
        $aggregateRootId = UuidAggregateRootId::fromString($id);

        $this->assertInstanceOf(UuidAggregateRootId::class, $aggregateRootId);
        $this->assertSame($id, (string) $aggregateRootId);
    }

    #[Test]
    public function equals_returns_expected(): void
    {
        $aggregateRootId1 = UuidAggregateRootId::new();
        $aggregateRootId2 = UuidAggregateRootId::new();

        $this->assertFalse($aggregateRootId1->isEqualTo($aggregateRootId2));

        $aggregateRootId1 = UuidAggregateRootId::new();
        $aggregateRootId2 = UuidAggregateRootId::fromString((string) $aggregateRootId1);

        $this->assertTrue($aggregateRootId1->isEqualTo($aggregateRootId2));
    }
}
