<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\CommandHandling;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\CommandHandling\CommandBus;
use Ssmiff\CqrsEs\CommandHandling\CommandHandler;
use Ssmiff\CqrsEs\CommandHandling\SimpleCommandBus;

#[CoversClass(SimpleCommandBus::class)]
final class SimpleCommandBusTest extends TestCase
{
    #[Test]
    public function it_dispatches_to_all_subscribed_handlers(): void
    {
        $bus = new SimpleCommandBus();

        $receivedA = [];
        $receivedB = [];

        $handlerA = new class($receivedA) implements CommandHandler {
            public function __construct(private array &$received) {}

            public function handle(object $command): void
            {
                $this->received[] = $command;
            }
        };
        $handlerB = new class($receivedB) implements CommandHandler {
            public function __construct(private array &$received) {}

            public function handle(object $command): void
            {
                $this->received[] = $command;
            }
        };

        $bus->subscribe($handlerA);
        $bus->subscribe($handlerB);

        $command1 = (object)['name' => 'c1'];
        $command2 = (object)['name' => 'c2'];

        $bus->dispatch($command1);
        $bus->dispatch($command2);

        self::assertSame([$command1, $command2], $receivedA);
        self::assertSame([$command1, $command2], $receivedB);
    }

    #[Test]
    public function it_queues_nested_dispatches_without_recursion(): void
    {
        $bus = new SimpleCommandBus();

        $handled = [];

        $reentrantHandler = new class($bus, $handled) implements CommandHandler {
            public function __construct(private CommandBus $bus, private array &$handled) {}

            public function handle(object $command): void
            {
                $this->handled[] = $command;
                if (isset($command->dispatchNext)) {
                    $this->bus->dispatch((object)['name' => 'nested']);
                }
            }
        };

        $bus->subscribe($reentrantHandler);

        $first = (object)['name' => 'first', 'dispatchNext' => true];
        $bus->dispatch($first);

        self::assertCount(2, $handled);
        self::assertSame('first', $handled[0]->name);
        self::assertSame('nested', $handled[1]->name);
    }
}
