<?php

namespace Ssmiff\CqrsEs\EventHandling;

use Ssmiff\CqrsEs\DomainEvent;
use Ssmiff\CqrsEs\DomainEventStream;

trait PubSubTrait
{
    /** @var EventListener[] */
    private array $eventListeners = [];

    /** @var DomainEvent[] */
    private array $eventQueue = [];

    private bool $isPublishing = false;

    public function subscribe(EventListener $eventListener): void
    {
        $this->eventListeners[] = $eventListener;
    }

    public function publish(DomainEventStream $eventStream): void
    {
        foreach ($eventStream as $event) {
            $this->eventQueue[] = $event;
        }

        if (!$this->isPublishing) {
            $this->isPublishing = true;

            try {
                while ($event = array_shift($this->eventQueue)) {
                    foreach ($this->eventListeners as $eventListener) {
                        $this->handleEvent($eventListener, $event);
                    }
                }
            } finally {
                $this->isPublishing = false;
            }
        }
    }
}
