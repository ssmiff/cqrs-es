<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;

interface Serializer
{
    /**
     * @throws SerializationException
     */
    public function serialize(object $object): array;

    /**
     * @param array $payload
     * @param class-string $objectType
     *
     * @throws SerializationException
     */
    public function deserialize(array $payload, string $objectType): object;
}
