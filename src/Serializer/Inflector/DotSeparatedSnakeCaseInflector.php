<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer\Inflector;

use function get_class;
use function preg_replace;
use function str_replace;
use function strtolower;
use function ucwords;

class DotSeparatedSnakeCaseInflector implements ClassNameInflector
{
    public function instanceToType(object $instance): string
    {
        return $this->classNameToType(get_class($instance));
    }

    public function classNameToType(string $className): string
    {
        $snakeCase = (string)preg_replace('/(.)(?=[A-Z])/u', '$1_', $className);

        return str_replace('\\_', '.', strtolower($snakeCase));
    }

    public function typeToClassName(string $type): string
    {
        $separated = str_replace('_', ' ', str_replace('.', '\\ ', $type));

        return str_replace(' ', '', ucwords($separated));
    }
}
