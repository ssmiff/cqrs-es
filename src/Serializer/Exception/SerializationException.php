<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer\Exception;

use Ssmiff\CqrsEs\Exception\RuntimeException;
use Ssmiff\CqrsEs\Serializer\Serializable;

final class SerializationException extends RuntimeException
{
    public static function notInstanceOfSerializable(string $object): self
    {
        return new self(
            'Object \'%s\' does not implement %s',
            [$object, Serializable::class],
        );
    }
}
