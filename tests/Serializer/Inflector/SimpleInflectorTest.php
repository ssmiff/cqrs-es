<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer\Inflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\ClassInflector\ClassNameInflector;
use Ssmiff\CqrsEs\ClassInflector\SimpleInflector;

#[CoversClass(SimpleInflector::class)]
class SimpleInflectorTest extends TestCase
{
    private SimpleInflector $inflector;

    protected function setUp(): void
    {
        $this->inflector = new SimpleInflector();
    }

    #[Test]
    public function instance_of_class_name_inflector(): void
    {
        $this->assertInstanceOf(ClassNameInflector::class, $this->inflector);
    }

    #[Test]
    public function instance_to_type_returns_class_name(): void
    {
        $obj = new class() {};

        $result = $this->inflector->instanceToType($obj);

        $this->assertSame(get_class($obj), $result);
    }

    #[Test]
    public function type_to_class_name_returns_same_string(): void
    {
        $type = 'Some\\Namespace\\ClassName';

        $result = $this->inflector->typeToClassName($type);

        $this->assertSame($type, $result);
    }
}
