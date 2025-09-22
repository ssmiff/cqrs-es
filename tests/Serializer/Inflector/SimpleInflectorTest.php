<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer\Inflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Serializer\Inflector\ClassNameInflector;
use Ssmiff\CqrsEs\Serializer\Inflector\SimpleInflector;

#[CoversClass(SimpleInflector::class)]
class SimpleInflectorTest extends TestCase
{
    private SimpleInflector $inflector;

    protected function setUp(): void
    {
        $this->inflector = new SimpleInflector();
    }

    public function testInstanceOfClassNameInflector(): void
    {
        $this->assertInstanceOf(ClassNameInflector::class, $this->inflector);
    }

    public function testInstanceToTypeReturnsClassName(): void
    {
        $obj = new class() {};

        $result = $this->inflector->instanceToType($obj);

        $this->assertSame(get_class($obj), $result);
    }

    public function testTypeToClassNameReturnsSameString(): void
    {
        $type = 'Some\\Namespace\\ClassName';

        $result = $this->inflector->typeToClassName($type);

        $this->assertSame($type, $result);
    }
}
