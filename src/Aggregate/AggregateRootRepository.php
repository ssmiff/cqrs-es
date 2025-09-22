<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Aggregate;

use Ssmiff\CqrsEs\Aggregate\Exception\AggregateNotFoundException;

interface AggregateRootRepository
{
    public function persist(AggregateRoot $aggregateRoot): void;

    /**
     * @throws AggregateNotFoundException
     */
    public function retrieve(AggregateRootId $aggregateRootId): AggregateRoot;
}
