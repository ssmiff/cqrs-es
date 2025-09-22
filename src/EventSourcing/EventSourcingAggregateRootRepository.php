<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing;

use Ssmiff\CqrsEs\Aggregate\AggregateRoot;
use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\Aggregate\AggregateRootRepository;
use Ssmiff\CqrsEs\Aggregate\Exception\AggregateNotFoundException;
use Ssmiff\CqrsEs\EventHandling\EventBus;
use Ssmiff\CqrsEs\EventSourcing\AggregateFactory\AggregateFactory;
use Ssmiff\CqrsEs\EventStore\EventStore;
use Ssmiff\CqrsEs\EventStore\Exception\EventStreamNotFoundException;
use Ssmiff\CqrsEs\DomainEventStream;
use Webmozart\Assert\Assert;

readonly class EventSourcingAggregateRootRepository implements AggregateRootRepository
{
    /**
     * @param class-string<EventSourcedAggregateRoot> $aggregateClass
     */
    public function __construct(
        private EventStore $eventStore,
        private EventBus $eventBus,
        private string $aggregateClass,
        private AggregateFactory $aggregateFactory,
        private array $eventStreamDecorators = [],
    ) {
        $this->assertExtendsEventSourcedAggregateRoot($aggregateClass);
        $this->assertEventStreamDecorators($this->eventStreamDecorators);
    }

    public function persist(AggregateRoot $aggregateRoot): void
    {
        Assert::isInstanceOf($aggregateRoot, $this->aggregateClass);

        $eventStream = $this->decorateForWrite(
            $aggregateRoot,
            $aggregateRoot->getUncommittedEvents(),
        );

        $this->eventStore->append($aggregateRoot->getAggregateRootId(), $eventStream);
        $this->eventBus->publish($eventStream);
    }

    public function retrieve(AggregateRootId $aggregateRootId): AggregateRoot
    {
        try {
            $eventStream = $this->eventStore->retrieve($aggregateRootId);

            return $this->aggregateFactory->create($this->aggregateClass, $eventStream);
        } catch (EventStreamNotFoundException $e) {
            throw AggregateNotFoundException::create($aggregateRootId, $e);
        }
    }

    private function assertExtendsEventSourcedAggregateRoot(string $class): void
    {
        Assert::subclassOf($class, EventSourcedAggregateRoot::class);
    }

    private function assertEventStreamDecorators(array $eventStreamDecorators): void
    {
        Assert::allIsInstanceOf($eventStreamDecorators, EventStreamDecorator::class);
    }

    private function decorateForWrite(AggregateRoot $aggregateRoot, DomainEventStream $eventStream): DomainEventStream
    {
        $aggregateType = get_class($aggregateRoot);
        $aggregateIdentifier = $aggregateRoot->getAggregateRootId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite($aggregateType, $aggregateIdentifier, $eventStream);
        }

        return $eventStream;
    }
}
