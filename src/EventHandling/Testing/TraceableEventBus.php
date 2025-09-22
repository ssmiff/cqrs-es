<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventHandling;

use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;
use Ssmiff\CqrsEs\Serializer\Serializable;

final class TraceableEventBus implements EventBus
{
    /** @var DomainEvent[] */
    private array $recorded = [];
    private bool $isTracingEnabled = false;

    public function __construct(private readonly EventBus $eventBus) {}

    /**
     * {@inheritdoc}
     */
    public function subscribe(EventListener $eventListener): void
    {
        $this->eventBus->subscribe($eventListener);
    }

    public function publish(DomainEventStream $eventStream): void
    {
        $this->eventBus->publish($eventStream);

        if (!$this->isTracingEnabled) {
            return;
        }

        foreach ($eventStream as $event) {
            $this->recorded[] = $event;
        }
    }

    /**
     * @return Serializable[] Payloads of the recorded events
     */
    public function getEvents(): array
    {
        return array_map(
            fn (DomainEvent $event) => $event->getPayload(),
            $this->recorded,
        );
    }

    /**
     * Start tracing.
     */
    public function enableTracing(): void
    {
        $this->isTracingEnabled = true;
    }
}
