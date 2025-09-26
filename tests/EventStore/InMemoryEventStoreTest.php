<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\EventStore;

use PHPUnit\Framework\Attributes\CoversClass;
use Ssmiff\CqrsEs\EventStore\InMemoryEventStore;
use Ssmiff\CqrsEs\EventStore\Testing\EventStoreTest;

#[CoversClass(InMemoryEventStore::class)]
final class InMemoryEventStoreTest extends EventStoreTest
{
    protected function setUp(): void
    {
        $this->eventStore = new InMemoryEventStore();
    }
}
