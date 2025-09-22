<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Visitor;

interface VisitsEvents
{
    public function visitEvents(Criteria $criteria, EventVisitor $eventVisitor): void;
}
