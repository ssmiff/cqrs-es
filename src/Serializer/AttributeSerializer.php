<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

use ReflectionClass;
use ReflectionException;
use Ssmiff\CqrsEs\Attributes\SerializableProperty;
use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;

readonly class AttributeSerializer implements Serializer
{
    public function serialize(object $object): array
    {
        $refClass = new ReflectionClass($object);
        $payload = [];

        foreach ($refClass->getProperties() as $property) {
            $attr = $property->getAttributes(SerializableProperty::class);
            if ($attr) {
                $payload[$property->getName()] = $property->getValue($object);
            }
        }

        return $payload;
    }

    /**
     * @param array $payload
     * @param class-string $objectType
     *
     * @return object
     *
     * @throws ReflectionException
     */
    public function deserialize(array $payload, string $objectType): object
    {
        if (!class_exists($objectType)) {
            throw SerializationException::classDoesntExist($objectType);
        }

        $refClass = new ReflectionClass($objectType);

        // Map serializedName => property
        $propertyMap = [];
        foreach ($refClass->getProperties() as $property) {
            $attr = $property->getAttributes(SerializableProperty::class);
            if ($attr) {
                $propertyMap[$property->getName()] = $property;
            }
        }

        $constructor = $refClass->getConstructor();
        if ($constructor) {
            $args = [];
            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();

                // Try to find a serialized property matching this parameter
                $value = null;
                foreach ($propertyMap as $serializedName => $property) {
                    if (strtolower($paramName) === strtolower($serializedName)
                        && array_key_exists($serializedName, $payload)) {
                        $value = $payload[$serializedName];
                        break;
                    }
                }

                if ($value !== null) {
                    $args[] = $value;
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    // Can't satisfy parameter, set null if allowed
                    $type = $param->getType();
                    if ($type && $type->allowsNull()) {
                        $args[] = null;
                    } else {
                        $args = null;
                        break;
                    }
                }
            }

            if ($args !== null) {
                $object = $refClass->newInstanceArgs($args);
            }
        }

        if (!isset($object)) {
            $object = $refClass->newInstanceWithoutConstructor();

            // Set properties directly
            foreach ($propertyMap as $serializedName => $property) {
                if (array_key_exists($serializedName, $payload)) {
                    $property->setValue($object, $payload[$serializedName]);
                }
            }
        }

        return $object;
    }
}
