<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

final readonly class DomainEventStream implements IteratorAggregate
{
    private array $events;

    /**
     * @param DomainEvent[] $events
     */
    public function __construct(array $events)
    {
        Assert::allIsInstanceOf($events, DomainEvent::class);

        $this->events = $events;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->events);
    }
}
