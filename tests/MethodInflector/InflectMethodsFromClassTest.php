<?php

declare(strict_types=1);

namespace MethodInflector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function instance_of_handle_method_inflector(): void
    {
        $this->assertInstanceOf(MethodInflector::class, $this->inflector);
    }

    #[Test]
    public function it_returns_method_name_when_method_exists(): void
    {
        $eventListener = new class {
            public function applySomeEvent(): void {}
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertSame(['applySomeEvent'], $methods);
    }

    #[Test]
    public function it_returns_null_when_method_does_not_exist(): void
    {
        $eventListener = new class {
            // no applyXxx method
        };

        $event = new SomeEvent();

        $methods = $this->inflector->handleMethods($eventListener, $event);

        $this->assertEmpty($methods);
    }
}

