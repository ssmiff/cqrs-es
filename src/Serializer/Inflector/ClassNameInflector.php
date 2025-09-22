<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer\Inflector;

interface ClassNameInflector
{
    public function instanceToType(object $instance): string;

    public function typeToClassName(string $type): string;
}
