<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;

use Ssmiff\CqrsEs\Serializer\Inflector\ClassNameInflector;
use Webmozart\Assert\Assert;

use function get_class;
use function in_array;

readonly class SimpleInterfaceSerializer implements Serializer
{
    public function __construct(private ClassNameInflector $classNameInflector) {}

    public function serialize($object): array
    {
        if (!$object instanceof Serializable) {
            throw SerializationException::notInstanceOfSerializable(get_class($object));
        }

        return [
            'type' => $this->classNameInflector->instanceToType($object),
            'payload' => $object->serialize(),
        ];
    }

    public function deserialize(array $serializedObject): object
    {
        Assert::keyExists($serializedObject, 'type', "Key 'type' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        /** @var Serializable $class */
        $class = $this->classNameInflector->typeToClassName($serializedObject['type']);

        if (!in_array(Serializable::class, class_implements($class))) {
            throw SerializationException::notInstanceOfSerializable($class);
        }

        return $class::deserialize($serializedObject['payload']);
    }
}
