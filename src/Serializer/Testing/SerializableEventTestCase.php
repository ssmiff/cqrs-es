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
use Ssmiff\CqrsEs\ClassInflector\SimpleInflector;
use Ssmiff\CqrsEs\Serializer\Serializable;
use Ssmiff\CqrsEs\Serializer\SimpleInterfaceSerializer;

/**
 * Helper to test if events implement the Serializable contract.
 */
abstract class SerializableEventTestCase extends TestCase
{
    #[Test]
    public function itsSerializable(): void
    {
        $this->assertInstanceOf(Serializable::class, $this->createEvent());
    }

    #[Test]
    public function serializingAndDeserializingYieldsTheSameObject(): void
    {
        $serializer = new SimpleInterfaceSerializer();
        $event = $this->createEvent();

        $serialized = $serializer->serialize($event);
        $deserialized = $serializer->deserialize($serialized, $event::class);

        $this->assertEquals($event, $deserialized);
    }

    abstract protected function createEvent(): object;
}
