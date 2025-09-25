<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Exception\RuntimeException;
use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;
use Ssmiff\CqrsEs\Serializer\Serializable;

#[CoversClass(SerializationException::class)]
class SerializationExceptionTest extends TestCase
{
    public function testNotInstanceOfSerializableCreatesException(): void
    {
        $object = 'NonSerializableClass';
        $expectedMessage = sprintf(
            "Object '%s' does not implement %s",
            $object,
            Serializable::class
        );

        $exception = SerializationException::notInstanceOfSerializable($object);

        $this->assertInstanceOf(SerializationException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testClassDoesntExistCreatesException(): void
    {
        $className = 'Missing\\ClassName';
        $expectedMessage = sprintf("Object class '%s' does not exist", $className);

        $exception = SerializationException::classDoesntExist($className);

        $this->assertInstanceOf(SerializationException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
    }
}
