<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventHandling;

use Ssmiff\CqrsEs\DomainEvent;

interface EventListener
{
    public function handle(DomainEvent $event): void;
}
