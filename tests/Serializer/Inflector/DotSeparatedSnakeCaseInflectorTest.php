<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer\Inflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Serializer\Inflector\ClassNameInflector;
use Ssmiff\CqrsEs\Serializer\Inflector\DotSeparatedSnakeCaseInflector;
use Ssmiff\CqrsEs\Tests\Stubs\SomeEvent;

#[CoversClass(DotSeparatedSnakeCaseInflector::class)]
class DotSeparatedSnakeCaseInflectorTest extends TestCase
{
    private DotSeparatedSnakeCaseInflector $inflector;

    protected function setUp(): void
    {
        $this->inflector = new DotSeparatedSnakeCaseInflector();
    }

    public function testInstanceOfClassNameInflector(): void
    {
        $this->assertInstanceOf(ClassNameInflector::class, $this->inflector);
    }

    public function testInstanceToTypeConvertsClassNameToDotSeparatedSnakeCase(): void
    {
        $object = new SomeEvent();

        $result = $this->inflector->instanceToType($object);

        $this->assertSame('ssmiff.cqrs_es.tests.stubs.some_event', $result);
    }

    public function testClassNameToTypeConvertsClassNameToDotSeparatedSnakeCase(): void
    {
        $className = 'Namespace\\SubNamespace\\MySampleClass';

        $this->assertSame(
            'namespace.sub_namespace.my_sample_class',
            $this->inflector->classNameToType($className),
        );
    }

    public function testTypeToClassNameConvertsDotSeparatedSnakeCaseToClassName(): void
    {
        $type = 'namespace.sub_namespace.my_sample_class';

        $this->assertSame(
            'Namespace\SubNamespace\MySampleClass',
            $this->inflector->typeToClassName($type),
        );
    }
}

class SampleTestClass {}
