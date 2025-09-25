<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing;

use Ssmiff\CqrsEs\MethodInflector\MethodInflector;
use Ssmiff\CqrsEs\EventSourcing\Exception\AggregateRootAlreadyRegisteredException;

interface EventSourcedEntity
{
    public function handleRecursively(object $eventPayload): void;

    /**
     * @throws AggregateRootAlreadyRegisteredException
     */
    public function registerAggregateRoot(
        EventSourcedAggregateRoot $aggregateRoot,
        MethodInflector $handleMethodInflector,
    ): void;
}
