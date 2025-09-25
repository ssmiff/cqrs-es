<?php

declare(strict_types=1);

namespace MethodInflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\MethodInflector\MethodInflector;
use Ssmiff\CqrsEs\MethodInflector\InflectMethodsFromClass;
use Ssmiff\CqrsEs\Tests\Stubs\SomeEvent;

#[CoversClass(InflectMethodsFromClass::class)]
class InflectMethodsFromClassTest extends TestCase
{
    private InflectMethodsFromClass $inflector;

    protected function setUp(): void
    {
        $this->inflector = new InflectMethodsFromClass();
    }

    public function testInstanceOfHandleMethodInflector(): void
    {
        $this->assertInstanceOf(MethodInflector::class, $this->inflector);
    }

    public function testReturnsMethodNameWhenMethodExists(): void
    {
        $eventListener = new class {
            public function applySomeEvent(): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertSame(['applySomeEvent'], $methods);
    }

    public function testReturnsNullWhenMethodDoesNotExist(): void
    {
        $eventListener = new class {
            // no applyXxx method
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEmpty($methods);
    }
}

