<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\MethodInflector;

use ReflectionClass;
use ReflectionMethod;
use Ssmiff\CqrsEs\Attributes\EventHandler;

class InflectMethodsFromAttribute implements MethodInflector
{
    public function handleMethods(object $eventListener, object $event): array
    {
        $class = new ReflectionClass($eventListener);
        $methods = [];

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(EventHandler::class);

            foreach ($attributes as $attribute) {
                /** @var EventHandler $instance */
                $instance = $attribute->newInstance();

                if ($instance->eventClass === get_class($event)) {
                    $methods[] = $method->getName();
                    break;
                }
            }
        }

        return $methods;
    }
}
