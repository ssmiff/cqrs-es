<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer\Inflector;

use function get_class;
use function preg_replace;
use function str_replace;
use function strtolower;
use function ucwords;

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
