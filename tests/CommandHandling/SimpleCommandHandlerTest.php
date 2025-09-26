<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\CommandHandling;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Attributes\EventHandler;
use Ssmiff\CqrsEs\CommandHandling\SimpleCommandHandler;
use Ssmiff\CqrsEs\Tests\Stubs\BarCommand;
use Ssmiff\CqrsEs\Tests\Stubs\FooCommand;

#[CoversClass(SimpleCommandHandler::class)]
final class SimpleCommandHandlerTest extends TestCase
{
    #[Test]
    public function it_invokes_methods_annotated_for_command_class(): void
    {
        $handled = [];

        $handler = new class($handled) extends SimpleCommandHandler {
            public function __construct(private array &$handled) {}

            #[EventHandler(FooCommand::class)]
            public function whenFoo(object $command): void { $this->handled[] = ['foo', $command]; }

            #[EventHandler(BarCommand::class)]
            public function whenBar(object $command): void { $this->handled[] = ['bar', $command]; }
        };

        $foo = new FooCommand();
        $bar = new BarCommand();

        $handler->handle($foo);
        $handler->handle($bar);

        self::assertCount(2, $handled);
        self::assertSame(['foo', $foo], $handled[0]);
        self::assertSame(['bar', $bar], $handled[1]);
    }
}
