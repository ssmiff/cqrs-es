<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Ssmiff\CqrsEs\ClassInflector\ClassNameInflector;
use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;
use Ssmiff\CqrsEs\Serializer\Serializable;
use Ssmiff\CqrsEs\Serializer\SimpleInterfaceSerializer;
use Ssmiff\CqrsEs\Tests\Stubs\NonSerializableClass;
use Ssmiff\CqrsEs\Tests\Stubs\NonSerializableDeserializeClass;
use Ssmiff\CqrsEs\Tests\Stubs\SerializableClass;

#[CoversClass(SimpleInterfaceSerializer::class)]
class SimpleInterfaceSerializerTest extends MockeryTestCase
{
    private SimpleInterfaceSerializer $serializer;

    private Mockery\MockInterface $inflectorMock;

    protected function setUp(): void
    {
        $this->inflectorMock = Mockery::mock(ClassNameInflector::class);

        $this->serializer = new SimpleInterfaceSerializer($this->inflectorMock);
    }

    #[Test]
    public function serialize_throws_exception_if_not_serializable(): void
    {
        $nonSerializableObject = new class {};

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessageMatches('/does not implement/');

        $this->serializer->serialize($nonSerializableObject);
    }

    #[Test]
    public function serialize_returns_serialized_array(): void
    {
        $serializableObject = new SerializableClass();

        $payload = $this->serializer->serialize($serializableObject);

        $expected = ['key' => 'value'];

        $this->assertSame($expected, $payload);
    }

    #[Test]
    public function deserialize_throws_exception_if_type_not_serializable(): void
    {
        $this->expectException(SerializationException::class);

        $this->serializer->deserialize(['a' => 1], NonSerializableDeserializeClass::class);
    }

    #[Test]
    public function deserialize_returns_object(): void
    {
        $this->inflectorMock->shouldIgnoreMissing();

        $obj = SerializableClass::deserialize(['key' => 'value']);
        $result = $this->serializer->deserialize(['key' => 'value'], SerializableClass::class);

        $this->assertInstanceOf(SerializableClass::class, $result);
        $this->assertEquals($obj, $result);
    }
}
