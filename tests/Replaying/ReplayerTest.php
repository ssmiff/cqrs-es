<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Replaying;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Replaying\Replayer;
use Ssmiff\CqrsEs\EventStore\Visitor\Criteria;
use Ssmiff\CqrsEs\EventStore\Visitor\EventVisitor;
use Ssmiff\CqrsEs\EventStore\Visitor\VisitsEvents;

#[CoversClass(Replayer::class)]
class ReplayerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[Test]
    public function replay_calls_visit_events_on_event_store(): void
    {
        // Create mocks with Mockery
        $eventStoreMock = Mockery::mock(VisitsEvents::class);
        $eventVisitorMock = Mockery::mock(EventVisitor::class);
        $criteriaMock = Mockery::mock(Criteria::class);

        $eventStoreMock
            ->shouldReceive('visitEvents')
            ->once()
            ->with($criteriaMock, $eventVisitorMock);

        // Instantiate Replayer with mocks
        $replayer = new Replayer($eventStoreMock, $eventVisitorMock);

        // Call replay method
        $replayer->replay($criteriaMock);
    }
}
