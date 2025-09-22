<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\HandleMethodInflector;

interface HandleMethodInflector
{
    public function handleMethods(object $eventListener, object $event): array;
}
