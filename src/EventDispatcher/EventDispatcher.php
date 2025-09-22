<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventDispatcher;

interface EventDispatcher
{
    public function dispatch(string $eventName, array $arguments): void;

    public function addListener(string $eventName, callable $callable): void;
}
