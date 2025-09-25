<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Attributes\SerializableProperty;
use Ssmiff\CqrsEs\Serializer\AttributeSerializer;
use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;
use Ssmiff\CqrsEs\Serializer\Inflector\SimpleInflector;

#[CoversClass(AttributeSerializer::class)]
class AttributeSerializerTest extends TestCase
{
    private AttributeSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new AttributeSerializer(new SimpleInflector());
    }

    public function testSerializeIncludesOnlyAnnotatedPropertiesAndType(): void
    {
        $obj = new class() {
            #[SerializableProperty]
            public string $firstName = 'Jane';

            #[SerializableProperty]
            public int $age = 30;

            public string $ignoreMe = 'nope';
        };

        $result = $this->serializer->serialize($obj);

        $this->assertSame($obj::class, $result['type']);
        $this->assertSame(['firstName' => 'Jane', 'age' => 30], $result['payload']);
        $this->assertArrayNotHasKey('ignoreMe', $result['payload']);
    }

    public function testDeserializeThrowsIfTypeMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'type' should be set.");

        $this->serializer->deserialize(['payload' => []]);
    }

    public function testDeserializeThrowsIfPayloadMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key 'payload' should be set.");

        $this->serializer->deserialize(['type' => 'Some\\Class']);
    }

    public function testDeserializeThrowsIfClassDoesNotExist(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('does not exist');

        $this->serializer->deserialize([
            'type' => 'Some\\Totally\\MissingClass',
            'payload' => [],
        ]);
    }

    public function testDeserializeViaConstructorWhenParametersMatchAnnotatedProperties(): void
    {
        $className = new class('Jane', 30) {
            #[SerializableProperty]
            public string $firstName;

            #[SerializableProperty]
            public int $age;

            public function __construct(string $firstName, int $age)
            {
                $this->firstName = $firstName;
                $this->age = $age;
            }
        };

        $serialized = [
            'type' => $className::class,
            'payload' => ['firstName' => 'Jane', 'age' => 30],
        ];

        $obj = $this->serializer->deserialize($serialized);

        $this->assertSame('Jane', $obj->firstName);
        $this->assertSame(30, $obj->age);
    }

    public function testDeserializeSetsPropertiesDirectlyWhenConstructorNotSatisfiable(): void
    {
        $className = new class('constructed') {
            #[SerializableProperty]
            public string $firstName;

            #[SerializableProperty]
            public int $age;

            public ?string $note = null;

            // Constructor cannot be satisfied from payload (param names differ)
            public function __construct(string $note)
            {
                $this->note = $note;
            }
        };

        $serialized = [
            'type' => $className::class,
            'payload' => ['firstName' => 'John', 'age' => 42],
        ];

        $obj = $this->serializer->deserialize($serialized);

        // Falls back to newInstanceWithoutConstructor; properties set directly
        $this->assertSame('John', $obj->firstName);
        $this->assertSame(42, $obj->age);
        $this->assertTrue(property_exists($obj, 'note'));
        $this->assertSame(null, $obj->note); // not set by serializer
    }
}
