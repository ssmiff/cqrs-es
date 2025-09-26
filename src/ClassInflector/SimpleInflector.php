<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\ClassInflector;

class SimpleInflector implements ClassNameInflector
{
    public function instanceToType(object $instance): string
    {
        return $instance::class;
    }

    public function typeToClassName(string $type): string
    {
        return $type;
    }
}
