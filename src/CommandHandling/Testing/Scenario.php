<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling\Testing;

use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\Aggregate\UuidAggregateRootId;
use Ssmiff\CqrsEs\CommandHandling\CommandHandler;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;
use Ssmiff\CqrsEs\EventStore\TraceableEventStore;
use Ssmiff\CqrsEs\Metadata;

/**
 * Helper testing scenario to test command handlers.
 *
 * The scenario will help with testing command handlers. A scenario consists of
 * three steps:
 *
 * 1) given(): Load a history of events in the event store
 * 2) when():  Dispatch a command
 * 3) then():  events that should have been persisted
 */
class Scenario
{
    private AggregateRootId $aggregateId;

    public function __construct(
        private readonly TestCase $testCase,
        private readonly TraceableEventStore $eventStore,
        private readonly CommandHandler $commandHandler,
    ) {
        $this->aggregateId = UuidAggregateRootId::new();
    }

    public function withAggregateId(AggregateRootId $aggregateId): self
    {
        $this->aggregateId = $aggregateId;
        return $this;
    }

    /**
     * @param object[] $givens
     */
    public function given(?array $givens): self
    {
        if (null === $givens) {
            return $this;
        }

        $events = [];
        $version = -1;

        foreach ($givens as $event) {
            ++$version;
            $events[] = DomainEvent::recordNow($this->aggregateId, $version, $event, new Metadata([]));
        }

        $this->eventStore->append($this->aggregateId, new DomainEventStream($events));

        return $this;
    }

    public function when(object $command): self
    {
        $this->eventStore->startTracing();

        $this->commandHandler->handle($command);

        return $this;
    }

    /**
     * @param object[] $events
     */
    public function then(array $events): self
    {
        $this->testCase->assertEquals($events, $this->eventStore->getEvents());

        $this->eventStore->clearEvents();

        return $this;
    }
}
