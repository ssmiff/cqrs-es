<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing\Testing;

use Exception;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\Aggregate\UuidAggregateRootId;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;
use Ssmiff\CqrsEs\EventSourcing\AggregateFactory\AggregateFactory;
use Ssmiff\CqrsEs\EventSourcing\EventSourcedAggregateRoot;
use Ssmiff\CqrsEs\Metadata;

/**
 * Helper testing scenario to test command event sourced aggregate roots.
 *
 * The scenario will help with testing event sourced aggregate roots. A
 * scenario consists of three steps:
 *
 * 1) given(): Initialize the aggregate root using a history of events
 * 2) when():  A callable that calls a method on the event sourced aggregate root
 * 3) then():  Events that should have been applied
 */
class Scenario
{
    private ?EventSourcedAggregateRoot $aggregateRootInstance = null;
    private AggregateRootId $aggregateId;

    public function __construct(
        private readonly TestCase $testCase,
        private readonly AggregateFactory $factory,
        private readonly string $aggregateRootClass
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

        $this->aggregateRootInstance = $this->factory->create(
            $this->aggregateRootClass, new DomainEventStream($events)
        );

        return $this;
    }

    public function when(callable $when): self
    {
        if (!is_callable($when)) {
            return $this;
        }

        if (null === $this->aggregateRootInstance) {
            $this->aggregateRootInstance = $when($this->aggregateRootInstance);

            $this->testCase->assertInstanceOf($this->aggregateRootClass, $this->aggregateRootInstance);
        } else {
            $when($this->aggregateRootInstance);
        }

        return $this;
    }

    /**
     * @param object[] $thens
     */
    public function then(array $thens): self
    {
        $this->testCase->assertEquals($thens, $this->getEvents());

        return $this;
    }

    /**
     * @return object[] Payloads of the recorded events
     */
    private function getEvents(): array
    {
        return array_map(
            fn (DomainEvent $event) => $event->getPayload(),
            iterator_to_array($this->aggregateRootInstance->getUncommittedEvents())
        );
    }
}
