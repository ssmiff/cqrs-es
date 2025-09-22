<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Exception;

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;

final class EventStreamNotFoundException extends EventStoreException
{
    private AggregateRootId $aggregateId;

    public static function withAggregateId(AggregateRootId $aggregateRootId): self
    {
        $exception = new self(
            'EventStream not found for aggregate with ID %s',
            [(string)$aggregateRootId],
        );

        $exception->aggregateId = $aggregateRootId;
        return $exception;
    }

    public static function withAggregateIdAndTable(AggregateRootId $aggregateRootId, string $table): self
    {
        $exception = new self(
            'EventStream not found for aggregate with ID %s for table %s',
            [(string)$aggregateRootId, $table],
        );

        $exception->aggregateId = $aggregateRootId;
        return $exception;
    }

    public function getAggregateId(): AggregateRootId
    {
        return $this->aggregateId;
    }
}
