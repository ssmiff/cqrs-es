<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Exception;

use Ssmiff\CqrsEs\DomainEventStream;
use Throwable;

final class DuplicateVersionException extends EventStoreException
{
    private DomainEventStream $eventStream;

    public static function forEventStream(DomainEventStream $eventStream, ?Throwable $previous): self
    {
        $exception = new self('Duplicate version found in event stream', previous: $previous);
        $exception->eventStream = $eventStream;

        return $exception;
    }

    public function getEventStream(): DomainEventStream
    {
        return $this->eventStream;
    }
}
