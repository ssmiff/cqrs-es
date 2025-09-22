<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Aggregate;

use Ssmiff\CqrsEs\DomainEventStream;

interface AggregateRoot
{
    public function getAggregateRootId(): AggregateRootId;

    public function getAggregateRootVersion(): int;

    public function getUncommittedEvents(): DomainEventStream;

    public function reconstituteFromEventStream(DomainEventStream $eventStream): void;
}
