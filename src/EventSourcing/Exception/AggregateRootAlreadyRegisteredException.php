<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing\Exception;

use Ssmiff\CqrsEs\EventSourcing\EventSourcedAggregateRoot;

class AggregateRootAlreadyRegisteredException extends EventSourcingException
{
    private EventSourcedAggregateRoot $aggregateRoot;

    public static function withEventSourcedAggregateRoot(EventSourcedAggregateRoot $aggregateRoot): self
    {
        $exception = new self('Aggregate root already registered:');
        $exception->aggregateRoot = $aggregateRoot;
        return $exception;
    }

    public function getAggregateRoot(): EventSourcedAggregateRoot
    {
        return $this->aggregateRoot;
    }
}
