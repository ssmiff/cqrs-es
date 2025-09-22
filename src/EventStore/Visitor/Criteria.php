<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Visitor;

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\DomainEvent;

interface Criteria
{
    /**
     * @return AggregateRootId[]
     */
    public function getAggregateRootIds(): array;

    /**
     * @return string[]
     */
    public function getAggregateRootIdTypes(): array;

    /**
     * @return string[]
     */
    public function getEventTypes(): array;

    public function isMatchedBy(DomainEvent $event): bool;
}
