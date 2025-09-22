<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventHandling;

use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\HandleMethodInflector\HandleMethodInflector;

abstract readonly class AbstractEventListener implements EventListener
{
    public function __construct(private HandleMethodInflector $handleMethodInflector) {}

    public function handle(DomainEvent $event): void
    {
        $methods = $this->handleMethodInflector->handleMethods($this, $event);

        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                $this->$method($event);
            }
        }
    }
}
