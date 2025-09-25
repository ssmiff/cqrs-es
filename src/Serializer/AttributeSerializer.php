<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

use ReflectionClass;
use Ssmiff\CqrsEs\Attributes\SerializableProperty;
use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;
use Ssmiff\CqrsEs\Serializer\Inflector\ClassNameInflector;
use Webmozart\Assert\Assert;

readonly class AttributeSerializer implements Serializer
{
    public function __construct(
        protected ClassNameInflector $classNameInflector,
    ) {}

    public function serialize($object): array
    {
        $refClass = new ReflectionClass($object);
        $payload = [];

        foreach ($refClass->getProperties() as $property) {
            $attr = $property->getAttributes(SerializableProperty::class);
            if ($attr) {
                $payload[$property->getName()] = $property->getValue($object);
            }
        }

        return [
            'type' => $this->classNameInflector->instanceToType($object),
            'payload' => $payload,
        ];
    }

    public function deserialize(array $serializedObject): object
    {
        Assert::keyExists($serializedObject, 'type', "Key 'type' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        $className = $this->classNameInflector->typeToClassName($serializedObject['type']);
        if (!class_exists($className)) {
            throw SerializationException::classDoesntExist($className);
        }

        $refClass = new ReflectionClass($className);
        $payload = $serializedObject['payload'];

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
