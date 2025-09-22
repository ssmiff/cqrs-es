<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Ssmiff\CqrsEs\Serializer\Serializable;
use Ssmiff\CqrsEs\Serializer\SimpleInterfaceSerializer;
use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;
use Ssmiff\CqrsEs\Serializer\Inflector\ClassNameInflector;
use Ssmiff\CqrsEs\Tests\Stubs\NonSerializableClass;
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

    public function testSerializeThrowsExceptionIfNotSerializable(): void
    {
        $nonSerializableObject = new class {};

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessageMatches('/does not implement/');

        $this->serializer->serialize($nonSerializableObject);
    }

    public function testSerializeReturnsSerializedArray(): void
    {
        $serializableObject = new class implements Serializable {
            public function serialize(): array
            {
                return ['key' => 'value'];
            }

            public static function deserialize(array $data): static
            {
                return new static();
            }
        };

        // Mock behavior for instanceToType
        $this->inflectorMock
            ->shouldReceive('instanceToType')
            ->once()
            ->with($serializableObject)
            ->andReturn('some.type');

        $result = $this->serializer->serialize($serializableObject);

        $expected = [
            'type' => 'some.type',
            'payload' => ['key' => 'value'],
        ];

        $this->assertSame($expected, $result);
    }

    public function testUnserializeThrowsExceptionIfTypeKeyIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'type' should be set.");

        $this->serializer->deserialize(['payload' => []]);
    }

    public function testUnserializeThrowsExceptionIfPayloadKeyIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'payload' should be set.");

        $this->serializer->deserialize(['type' => 'some.type']);
    }

    public function testUnserializeThrowsExceptionIfClassNotSerializable(): void
    {
        // Mock behavior for typeToClassName
        $this->inflectorMock
            ->expects('typeToClassName')
            ->with('some.type')
            ->andReturn(NonSerializableClass::class);

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('does not implement');

        $this->serializer->deserialize([
            'type' => 'some.type',
            'payload' => [],
        ]);
    }

    public function testUnserializeReturnsDeserializedObject(): void
    {
        // Mock behavior for typeToClassName
        $this->inflectorMock
            ->expects('typeToClassName')
            ->with('some.type')
            ->andReturn(SerializableClass::class);

        $result = $this->serializer->deserialize([
            'type' => 'some.type',
            'payload' => ['key' => 'value'],
        ]);

        $this->assertInstanceOf(SerializableClass::class, $result);
    }
}
