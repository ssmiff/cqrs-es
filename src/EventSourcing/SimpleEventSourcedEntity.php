<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing;

use Exception;
use Ssmiff\CqrsEs\EventSourcing\Exception\AggregateRootAlreadyRegisteredException;
use Ssmiff\CqrsEs\HandleMethodInflector\HandleMethodInflector;

abstract class SimpleEventSourcedEntity implements EventSourcedEntity
{
    private ?EventSourcedAggregateRoot $aggregateRoot = null;

    private HandleMethodInflector $handleMethodInflector;

    public function handleRecursively(object $eventPayload): void
    {
        $this->handle($eventPayload);

        foreach ($this->getChildEntities() as $entity) {
            $entity->registerAggregateRoot($this->aggregateRoot, $this->handleMethodInflector);
            $entity->handleRecursively($eventPayload);
        }
    }

    protected function handle(object $event): void
    {
        $methods = $this->handleMethodInflector->handleMethods($this, $event);

        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                $this->$method($event);
            }
        }
    }

    public function registerAggregateRoot(
        EventSourcedAggregateRoot $aggregateRoot,
        HandleMethodInflector $handleMethodInflector,
    ): void {
        if (null !== $this->aggregateRoot && $this->aggregateRoot !== $aggregateRoot) {
            throw AggregateRootAlreadyRegisteredException::withEventSourcedAggregateRoot($this->aggregateRoot);
        }

        $this->aggregateRoot = $aggregateRoot;
        $this->handleMethodInflector = $handleMethodInflector;
    }

    /**
     * @throws Exception
     */
    protected function apply(object $event): void
    {
        $this->aggregateRoot->recordThat($event);
    }

    /**
     * Returns all child entities.
     *
     * @return EventSourcedEntity[]
     */
    protected function getChildEntities(): array
    {
        return [];
    }
}

