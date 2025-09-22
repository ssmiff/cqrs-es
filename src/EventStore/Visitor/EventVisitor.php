<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Visitor;

use Ssmiff\CqrsEs\DomainEvent;

interface EventVisitor
{
    public function visitEvent(DomainEvent $event): void;
}
