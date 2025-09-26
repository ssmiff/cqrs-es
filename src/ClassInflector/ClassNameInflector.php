<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\ClassInflector;

interface ClassNameInflector
{
    public function instanceToType(object $instance): string;

    public function typeToClassName(string $type): string;
}
