<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventDispatcher;

final class TraceableEventDispatcher implements EventDispatcher
{
    private array $dispatchedEvents = [];

    public function dispatch(string $eventName, array $arguments): void
    {
        $this->dispatchedEvents[] = ['event' => $eventName, 'arguments' => $arguments];
    }

    public function addListener(string $eventName, callable $callable): void
    {
        // Do nothing
    }

    public function getDispatchedEvents(): array
    {
        return $this->dispatchedEvents;
    }
}
