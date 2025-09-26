<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer\Inflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\ClassInflector\ClassNameInflector;
use Ssmiff\CqrsEs\ClassInflector\DotSeparatedSnakeCaseInflector;
use Ssmiff\CqrsEs\Tests\Stubs\SomeEvent;

#[CoversClass(DotSeparatedSnakeCaseInflector::class)]
class DotSeparatedSnakeCaseInflectorTest extends TestCase
{
    private DotSeparatedSnakeCaseInflector $inflector;

    protected function setUp(): void
    {
        $this->inflector = new DotSeparatedSnakeCaseInflector();
    }

    #[Test]
    public function instance_of_class_name_inflector(): void
    {
        $this->assertInstanceOf(ClassNameInflector::class, $this->inflector);
    }

    #[Test]
    public function instance_to_type_converts_class_name_to_dot_separated_snake_case(): void
    {
        $object = new SomeEvent();

        $result = $this->inflector->instanceToType($object);

        $this->assertSame('ssmiff.cqrs_es.tests.stubs.some_event', $result);
    }

    #[Test]
    public function class_name_to_type_converts_class_name_to_dot_separated_snake_case(): void
    {
        $className = 'Namespace\\SubNamespace\\MySampleClass';

        $this->assertSame(
            'namespace.sub_namespace.my_sample_class',
            $this->inflector->classNameToType($className),
        );
    }

    #[Test]
    public function type_to_class_name_converts_dot_separated_snake_case_to_class_name(): void
    {
        $type = 'namespace.sub_namespace.my_sample_class';

        $this->assertSame(
            'Namespace\SubNamespace\MySampleClass',
            $this->inflector->typeToClassName($type),
        );
    }
}
