<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\MethodInflector;

interface MethodInflector
{
    public function handleMethods(object $eventListener, object $event): array;
}
