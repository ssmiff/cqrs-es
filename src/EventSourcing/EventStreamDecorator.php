<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing;

use Ssmiff\CqrsEs\DomainEventStream;

interface EventStreamDecorator
{
    public function decorateForWrite(
        string $aggregateType,
        string $aggregateIdentifier,
        DomainEventStream $eventStream,
    ): DomainEventStream;
}
