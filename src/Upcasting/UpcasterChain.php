<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Upcasting;

use Ssmiff\CqrsEs\DomainEvent;

interface UpcasterChain
{
    public function upcast(DomainEvent $event): DomainEvent;
}
