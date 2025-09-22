<?php

declare(strict_types=1);

namespace HandleMethodInflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\HandleMethodInflector\HandleMethodInflector;
use Ssmiff\CqrsEs\HandleMethodInflector\InflectHandlerMethodsFromClass;
use Ssmiff\CqrsEs\Tests\Stubs\SomeEvent;

#[CoversClass(InflectHandlerMethodsFromClass::class)]
class InflectHandlerMethodsFromClassTest extends TestCase
{
    private InflectHandlerMethodsFromClass $inflector;

    protected function setUp(): void
    {
        $this->inflector = new InflectHandlerMethodsFromClass();
    }

    public function testInstanceOfHandleMethodInflector(): void
    {
        $this->assertInstanceOf(HandleMethodInflector::class, $this->inflector);
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

