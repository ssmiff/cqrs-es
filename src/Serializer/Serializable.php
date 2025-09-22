<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

interface Serializable
{
    public function serialize(): array;

    public static function deserialize(array $data): static;
}
