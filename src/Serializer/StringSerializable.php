<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

interface StringSerializable
{
    public static function fromString(string $id): self;

    public function __toString(): string;
}
