<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Serializer;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Attributes\SerializableProperty;
use Ssmiff\CqrsEs\Serializer\AttributeSerializer;
use Ssmiff\CqrsEs\Serializer\Exception\SerializationException;

#[CoversClass(AttributeSerializer::class)]
class AttributeSerializerTest extends TestCase
{
    private AttributeSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new AttributeSerializer();
    }

    #[Test]
    public function serialize_includes_only_annotated_properties_and_type(): void
    {
        $obj = new class() {
            #[SerializableProperty]
            public string $firstName = 'Jane';

            #[SerializableProperty]
            public int $age = 30;

            public string $ignoreMe = 'nope';
        };

        $payload = $this->serializer->serialize($obj);

        $this->assertSame(['firstName' => 'Jane', 'age' => 30], $payload);
    }

    #[Test]
    public function deserialize_via_constructor_when_parameters_match_annotated_properties(): void
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

        $payload = ['firstName' => 'Jane', 'age' => 30];

        $obj = $this->serializer->deserialize($payload, $className::class);

        $this->assertSame('Jane', $obj->firstName);
        $this->assertSame(30, $obj->age);
    }

    #[Test]
    public function deserialize_sets_properties_directly_when_constructor_not_satisfiable(): void
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

        $payload = ['firstName' => 'John', 'age' => 42];

        $obj = $this->serializer->deserialize($payload, $className::class);

        // Falls back to newInstanceWithoutConstructor; properties set directly
        $this->assertSame('John', $obj->firstName);
        $this->assertSame(42, $obj->age);
        $this->assertTrue(property_exists($obj, 'note'));
        $this->assertSame(null, $obj->note); // not set by serializer
    }
}
