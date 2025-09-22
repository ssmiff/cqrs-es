<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventHandling;

use Ssmiff\CqrsEs\DomainEvent;

class SimpleEventBus implements EventBus
{
    use PubSubTrait;

    protected function handleEvent(EventListener $eventListener, DomainEvent $event): void
    {
        if ($eventListener instanceof ProcessorEventListener && false === ProcessorsEnabled::isEnabled()) {
            return;
        }

        $eventListener->handle($event);
    }
}
