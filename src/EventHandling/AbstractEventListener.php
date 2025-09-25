<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventHandling;

use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\MethodInflector\MethodInflector;

abstract readonly class AbstractEventListener implements EventListener
{
    public function __construct(private MethodInflector $methodInflector) {}

    public function handle(DomainEvent $event): void
    {
        $methods = $this->methodInflector->handleMethods($this, $event);

        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                $this->$method($event);
            }
        }
    }
}
