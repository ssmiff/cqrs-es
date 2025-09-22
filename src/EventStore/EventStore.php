<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore;

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\EventStore\Exception\DuplicateVersionException;
use Ssmiff\CqrsEs\EventStore\Exception\EventStreamNotFoundException;
use Ssmiff\CqrsEs\DomainEventStream;

interface EventStore
{
    /**
     * @throws EventStreamNotFoundException
     */
    public function retrieve(AggregateRootId $aggregateRootId): DomainEventStream;

    /**
     * @throws EventStreamNotFoundException
     */
    public function retrieveFromVersion(AggregateRootId $aggregateRootId, int $version): DomainEventStream;

    /**
     * @throws DuplicateVersionException
     */
    public function append(AggregateRootId $aggregateRootId, DomainEventStream $eventStream): void;
}
