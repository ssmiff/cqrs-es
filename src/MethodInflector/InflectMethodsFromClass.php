<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\MethodInflector;

class InflectMethodsFromClass implements MethodInflector
{
    public function handleMethods(object $eventListener, object $event): array
    {
        $classParts = explode('\\', get_class($event));
        $method = 'apply' . end($classParts);

        if (!method_exists($eventListener, $method)) {
            return [];
        }

        return [$method];
    }
}
