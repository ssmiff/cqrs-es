<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Exception;

use RuntimeException as PhpRuntimeException;
use Throwable;

abstract class RuntimeException extends PhpRuntimeException
{
    protected function __construct(
        string $message = "",
        array $args = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf($message, ...$args),
            $code,
            $previous
        );
    }
}
