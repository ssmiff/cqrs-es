<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Ssmiff\CqrsEs\ClassInflector\ClassNameInflector;
use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;
use Ssmiff\CqrsEs\Serializer\Serializable;
use Ssmiff\CqrsEs\Serializer\SimpleInterfaceSerializer;
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

        $payload = $this->serializer->serialize($serializableObject);

        $expected = ['key' => 'value'];

        $this->assertSame($expected, $payload);
    }
}
