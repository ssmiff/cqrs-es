<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\MethodInflector;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

class InflectMethodsFromReflection implements MethodInflector
{
    public function handleMethods(object $eventListener, object $event): array
    {
        $eventClassName = get_class($event);
        $class = new ReflectionClass($eventListener);
        $methods = [];

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $params = $method->getParameters();

            foreach ($params as $param) {
                $type = $param->getType();

                if ($type instanceof ReflectionNamedType
                    && $type->getName() === $eventClassName
                    && !$type->isBuiltin()
                ) {
                    $methods[] = $method->getName();
                    break;
                } elseif ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
                    foreach ($type->getTypes() as $subType) {
                        if ($subType->getName() === $eventClassName && !$subType->isBuiltin()) {
                            $methods[] = $method->getName();
                            break 2;
                        }
                    }
                }
            }
        }

        return $methods;
    }
}
