<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer\Testing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Serializer\Inflector\SimpleInflector;
use Ssmiff\CqrsEs\Serializer\Serializable;
use Ssmiff\CqrsEs\Serializer\SimpleInterfaceSerializer;

/**
 * Helper to test if events implement the Serializable contract.
 */
abstract class SerializableEventTestCase extends TestCase
{
    #[Test]
    public function its_serializable(): void
    {
        $this->assertInstanceOf(Serializable::class, $this->createEvent());
    }

    #[Test]
    public function serializing_and_deserializing_yields_the_same_object(): void
    {
        $serializer = new SimpleInterfaceSerializer(new SimpleInflector());
        $event = $this->createEvent();

        $serialized = $serializer->serialize($event);
        $deserialized = $serializer->deserialize($serialized);

        $this->assertEquals($event, $deserialized);
    }

    abstract protected function createEvent(): object;
}
