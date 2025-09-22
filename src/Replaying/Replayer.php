<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Replaying;

use Ssmiff\CqrsEs\EventStore\Visitor\Criteria;
use Ssmiff\CqrsEs\EventStore\Visitor\EventVisitor;
use Ssmiff\CqrsEs\EventStore\Visitor\VisitsEvents;

final readonly class Replayer
{
    public function __construct(
        private VisitsEvents $eventStore,
        private EventVisitor $eventVisitor,
    ) {}

    public function replay(Criteria $criteria): void
    {
        $this->eventStore->visitEvents($criteria, $this->eventVisitor);
    }
}
