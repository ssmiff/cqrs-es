<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventHandling;

use Psr\Log\LoggerInterface;
use Ssmiff\CqrsEs\DomainEvent;
use Throwable;

class ReliableEventBus implements EventBus
{
    use PubSubTrait;

    public function __construct(private readonly LoggerInterface $logger) {}

    protected function handleEvent(EventListener $eventListener, DomainEvent $event): void
    {
        if ($eventListener instanceof ProcessorEventListener && false === ProcessorsEnabled::isEnabled()) {
            $this->logger->debug(
                sprintf(
                    '[Event Listener]: %s, skipped as processors are disabled',
                    get_class($eventListener),
                )
            );

            return;
        }

        try {
            $eventListener->handle($event);
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf(
                    '[Event Listener]: %s, failed with message %s',
                    get_class($eventListener),
                    $exception->getMessage(),
                ),
                [
                    'exception' => $exception,
                ],
            );
        }
    }
}
