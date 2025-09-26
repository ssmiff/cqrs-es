<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;

use function get_class;
use function in_array;

readonly class SimpleInterfaceSerializer implements Serializer
{
    public function serialize(object $object): array
    {
        if (!$object instanceof Serializable) {
            throw SerializationException::notInstanceOfSerializable(get_class($object));
        }

        return $object->serialize();
    }

    /**
     * @param array $payload
     * @param class-string $objectType
     */
    public function deserialize(array $payload, string $objectType): object
    {
        if (!in_array(Serializable::class, class_implements($objectType))) {
            throw SerializationException::notInstanceOfSerializable($objectType);
        }

        return $objectType::deserialize($payload);
    }
}
