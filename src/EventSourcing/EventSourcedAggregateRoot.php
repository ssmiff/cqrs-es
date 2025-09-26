<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing;

use Exception;
use Ssmiff\CqrsEs\Aggregate\AggregateRoot;
use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;
use Ssmiff\CqrsEs\MethodInflector\InflectMethodsFromClass;
use Ssmiff\CqrsEs\MethodInflector\MethodInflector;

abstract class EventSourcedAggregateRoot implements AggregateRoot
{
    private static ?MethodInflector $methodInflector = null;

    /**
     * @var array<DomainEvent>
     */
    private array $uncommittedEvents = [];

    /*
     * 0-based event version
     */
    private int $version = -1;

    public function getAggregateRootVersion(): int
    {
        return $this->version;
    }

    public function getUncommittedEvents(): DomainEventStream
    {
        $stream = new DomainEventStream($this->uncommittedEvents);

        $this->uncommittedEvents = [];

        return $stream;
    }

    public function reconstituteFromEventStream(DomainEventStream $eventStream): void
    {
        foreach ($eventStream as $event) {
            ++$this->version;
            $this->handleRecursively($event->getPayload());
        }
    }

    /**
     * Applies an event. The event is added to the AggregateRoot's list of uncommitted events.
     *
     * @throws Exception
     */
    public function recordThat(object $eventPayload): void
    {
        $this->handleRecursively($eventPayload);

        ++$this->version;
        $this->uncommittedEvents[] = DomainEvent::recordNow(
            $this->getAggregateRootId(),
            $this->version,
            $eventPayload,
        );
    }

    protected function handleRecursively(object $eventPayload): void
    {
        $this->handle($eventPayload);

        foreach ($this->getChildEntities() as $entity) {
            $entity->registerAggregateRoot($this, self::getHandleMethodInflector());
            $entity->handleRecursively($eventPayload);
        }
    }

    protected function handle(object $event): void
    {
        $methods = self::getHandleMethodInflector()->handleMethods($this, $event);

        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                $this->$method($event);
            }
        }
    }

    public static function setHandleMethodInflector(MethodInflector $methodInflector): void
    {
        static::$methodInflector = $methodInflector;
    }

    private static function getHandleMethodInflector(): MethodInflector
    {
        return static::$methodInflector ?? new InflectMethodsFromClass();
    }

    /**
     * Override this method if your aggregate root contains child entities.
     *
     * @return EventSourcedEntity[]
     */
    protected function getChildEntities(): array
    {
        return [];
    }
}
