<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Visitor;

use Closure;
use Ssmiff\CqrsEs\DomainEvent;

final readonly class CallableVisitor implements EventVisitor
{
    public function __construct(private Closure $callable) {}

    public function visitEvent(DomainEvent $event): void
    {
        call_user_func($this->callable, $event);
    }
}
