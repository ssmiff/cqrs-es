<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Upcasting;

use Ssmiff\CqrsEs\DomainEvent;

final readonly class SequentialUpcasterChain implements UpcasterChain
{
    /**
     * @param Upcaster[] $upcasterList
     */
    public function __construct(private array $upcasterList)
    {
    }

    public function upcast(DomainEvent $event): DomainEvent
    {
        foreach ($this->upcasterList as $upcaster) {
            if ($upcaster->supports($event)) {
                $event = $upcaster->upcast($event);
            }
        }

        return $event;
    }
}
