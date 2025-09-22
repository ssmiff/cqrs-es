<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\HandleMethodInflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Attributes\EventHandler;
use Ssmiff\CqrsEs\HandleMethodInflector\HandleMethodInflector;
use Ssmiff\CqrsEs\HandleMethodInflector\InflectHandlerMethodsFromAttribute;
use Ssmiff\CqrsEs\Tests\Stubs\OtherEvent;
use Ssmiff\CqrsEs\Tests\Stubs\SomeEvent;

#[CoversClass(InflectHandlerMethodsFromAttribute::class)]
class InflectHandlerMethodsFromAttributeTest extends TestCase
{
    private InflectHandlerMethodsFromAttribute $inflector;

    protected function setUp(): void
    {
        $this->inflector = new InflectHandlerMethodsFromAttribute();
    }

    public function testInstanceOfHandleMethodInflector(): void
    {
        $this->assertInstanceOf(HandleMethodInflector::class, $this->inflector);
    }

    public function testItFindsMethodsWithMatchingEventHandlerAttribute(): void
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

    public function testItFindsNoMethodsWhenNoMatchingAttributesExist(): void
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

    public function testItHandlesMultipleMatchingMethods(): void
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
