<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\HandleMethodInflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Attributes\EventHandler;
use Ssmiff\CqrsEs\MethodInflector\MethodInflector;
use Ssmiff\CqrsEs\MethodInflector\InflectMethodsFromAttribute;
use Ssmiff\CqrsEs\Tests\Stubs\OtherEvent;
use Ssmiff\CqrsEs\Tests\Stubs\SomeEvent;

#[CoversClass(InflectMethodsFromAttribute::class)]
class InflectMethodsFromAttributeTest extends TestCase
{
    private InflectMethodsFromAttribute $inflector;

    protected function setUp(): void
    {
        $this->inflector = new InflectMethodsFromAttribute();
    }

    #[Test]
    public function test_instance_of_handle_method_inflector(): void
    {
        $this->assertInstanceOf(MethodInflector::class, $this->inflector);
    }

    #[Test]
    public function it_finds_methods_with_matching_event_handler_attribute(): void
    {
        $eventListener = new class {
            #[EventHandler(SomeEvent::class)]
            public function handleSomeEvent(SomeEvent $event): void {}

            #[EventHandler(OtherEvent::class)]
            public function handleOtherEvent(OtherEvent $event): void {}

            public function unrelatedMethod(string $param): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEquals(
            ['handleSomeEvent'],
            $methods,
            'Should only match the method with the EventHandler attribute for SomeEvent.',
        );
    }

    #[Test]
    public function it_finds_no_methods_when_no_matching_attributes_exist(): void
    {
        $eventListener = new class {
            #[EventHandler(OtherEvent::class)]
            public function handleOtherEvent(OtherEvent $event): void {}

            public function unrelatedMethod(string $param): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEmpty($methods, 'Should return an empty array when no EventHandler attribute matches the event.');
    }

    #[Test]
    public function it_handles_multiple_matching_methods(): void
    {
        $eventListener = new class {
            #[EventHandler(SomeEvent::class)]
            public function handleSomeEventOne(SomeEvent $event): void {}

            #[EventHandler(SomeEvent::class)]
            public function handleSomeEventTwo(SomeEvent $event): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEquals(
            ['handleSomeEventOne', 'handleSomeEventTwo'],
            $methods,
            'Should find all methods with matching EventHandler attributes for SomeEvent.',
        );
    }
}
