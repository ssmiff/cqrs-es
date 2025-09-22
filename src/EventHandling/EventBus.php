<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventHandling;

use Ssmiff\CqrsEs\DomainEventStream;

interface EventBus
{
    public function subscribe(EventListener $eventListener): void;

    public function publish(DomainEventStream $eventStream): void;
}
