<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;

interface Serializer
{
    /**
     * @throws SerializationException
     */
    public function serialize($object): array;

    /**
     * @throws SerializationException
     */
    public function deserialize(array $serializedObject): object;
}
