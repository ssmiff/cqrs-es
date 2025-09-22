<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventDispatcher;

final class CallableEventDispatcher implements EventDispatcher
{
    /** @var callable[] */
    private array $listeners = [];

    public function dispatch(string $eventName, array $arguments): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            call_user_func_array($listener, $arguments);
        }
    }

    public function addListener(string $eventName, callable $callable): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = $callable;
    }
}
