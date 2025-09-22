<?php

declare(strict_types=1);

namespace HandleMethodInflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\HandleMethodInflector\HandleMethodInflector;
use Ssmiff\CqrsEs\HandleMethodInflector\InflectHandlerMethodsFromReflection;
use Ssmiff\CqrsEs\Tests\Stubs\EventImplementingInterface;
use Ssmiff\CqrsEs\Tests\Stubs\OtherEvent;
use Ssmiff\CqrsEs\Tests\Stubs\SomeEvent;
use Ssmiff\CqrsEs\Tests\Stubs\SomeInterface;

#[CoversClass(InflectHandlerMethodsFromReflection::class)]
class InflectHandlerMethodsFromReflectionTest extends TestCase
{
    private InflectHandlerMethodsFromReflection $inflector;

    protected function setUp(): void
    {
        $this->inflector = new InflectHandlerMethodsFromReflection();
    }

    public function testInstanceOfHandleMethodInflector(): void
    {
        $this->assertInstanceOf(HandleMethodInflector::class, $this->inflector);
    }

    public function testItFindsMatchingMethodsWithNamedType(): void
    {
        $eventListener = new class {
            public function onEvent(SomeEvent $event): void {}

            public function onOtherEvent(OtherEvent $event): void {}

            public function unrelatedMethod(string $param): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEquals(['onEvent'], $methods, 'Should only match methods accepting SomeEvent.');
    }

    public function testItFindsMethodsWithUnionType(): void
    {
        $eventListener = new class {
            public function onEvent(SomeEvent|OtherEvent $event): void {}

            public function unrelatedMethod(string $param): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEquals(['onEvent'], $methods, 'Should match methods accepting SomeEvent via a union type.');
    }

    public function testItFindsMethodsWithIntersectionType(): void
    {
        $eventListener = new class {
            public function onEvent(EventImplementingInterface&SomeInterface $event): void {}

            public function unrelatedMethod(string $param): void {}
        };

        $event = new EventImplementingInterface();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEquals(['onEvent'],
            $methods,
            'Should match methods accepting SomeEvent via an intersection type.');
    }

    public function testItReturnsEmptyArrayForNoMatches(): void
    {
        $eventListener = new class {
            public function unrelatedMethod(string $param): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEmpty($methods, 'Should return an empty array when no methods match.');
    }
}
