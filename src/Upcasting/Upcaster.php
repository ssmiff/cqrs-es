<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Upcasting;

use Ssmiff\CqrsEs\DomainEvent;

interface Upcaster
{
    public function supports(DomainEvent $event): bool;

    public function upcast(DomainEvent $event): DomainEvent;
}
