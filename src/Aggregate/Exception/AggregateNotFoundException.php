<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Aggregate\Exception;

use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Throwable;

final class AggregateNotFoundException extends AggregateException
{
    public static function create(
        AggregateRootId $aggregateRootId,
        ?Throwable $previous = null,
    ): self {
        return new self(
            'Aggregate with ID %s not found',
            [(string)$aggregateRootId],
            0,
            $previous,
        );
    }
}

