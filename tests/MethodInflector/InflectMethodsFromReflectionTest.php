<?php

declare(strict_types=1);

namespace MethodInflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\MethodInflector\MethodInflector;
use Ssmiff\CqrsEs\MethodInflector\InflectMethodsFromReflection;
use Ssmiff\CqrsEs\Tests\Stubs\EventImplementingInterface;
use Ssmiff\CqrsEs\Tests\Stubs\OtherEvent;
use Ssmiff\CqrsEs\Tests\Stubs\SomeEvent;
use Ssmiff\CqrsEs\Tests\Stubs\SomeInterface;

#[CoversClass(InflectMethodsFromReflection::class)]
class InflectMethodsFromReflectionTest extends TestCase
{
    private InflectMethodsFromReflection $inflector;

    protected function setUp(): void
    {
        $this->inflector = new InflectMethodsFromReflection();
    }

    #[Test]
    public function instance_of_handle_method_inflector(): void
    {
        $this->assertInstanceOf(MethodInflector::class, $this->inflector);
    }

    #[Test]
    public function it_finds_matching_methods_with_named_type(): void
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

    #[Test]
    public function it_finds_methods_with_union_type(): void
    {
        $eventListener = new class {
            public function onEvent(SomeEvent|OtherEvent $event): void {}

            public function unrelatedMethod(string $param): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEquals(['onEvent'], $methods, 'Should match methods accepting SomeEvent via a union type.');
    }

    #[Test]
    public function it_finds_methods_with_intersection_type(): void
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

    #[Test]
    public function it_returns_empty_array_for_no_matches(): void
    {
        $eventListener = new class {
            public function unrelatedMethod(string $param): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEmpty($methods, 'Should return an empty array when no methods match.');
    }
}
