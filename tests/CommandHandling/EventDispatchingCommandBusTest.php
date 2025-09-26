<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\CommandHandling;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\CommandHandling\EventDispatchingCommandBus;
use Ssmiff\CqrsEs\CommandHandling\SimpleCommandBus;
use Ssmiff\CqrsEs\CommandHandling\CommandHandler;
use Ssmiff\CqrsEs\EventDispatcher\CallableEventDispatcher;

#[CoversClass(EventDispatchingCommandBus::class)]
final class EventDispatchingCommandBusTest extends TestCase
{
    #[Test]
    public function success_event_is_dispatched_after_handling(): void
    {
        $inner = new SimpleCommandBus();
        $dispatcher = new CallableEventDispatcher();
        $bus = new EventDispatchingCommandBus($inner, $dispatcher);

        $events = [];
        $dispatcher->addListener(EventDispatchingCommandBus::EVENT_COMMAND_SUCCESS, function ($command) use (&$events) {
            $events[] = ['event' => EventDispatchingCommandBus::EVENT_COMMAND_SUCCESS, 'arguments' => ['command' => $command]];
        });

        $handled = false;
        $handler = new class($handled) implements CommandHandler {
            public function __construct(private bool &$handled) {}
            public function handle(object $command): void { $this->handled = true; }
        };

        $inner->subscribe($handler);

        $command = (object)['name' => 'ok'];
        $bus->dispatch($command);

        self::assertCount(1, $events);
        self::assertSame(EventDispatchingCommandBus::EVENT_COMMAND_SUCCESS, $events[0]['event']);
        self::assertSame($command, $events[0]['arguments']['command']);
        self::assertTrue($handled);
    }

    #[Test]
    public function failure_event_is_dispatched_and_exception_is_rethrown(): void
    {
        $inner = new SimpleCommandBus();
        $dispatcher = new CallableEventDispatcher();
        $bus = new EventDispatchingCommandBus($inner, $dispatcher);

        $events = [];
        $dispatcher->addListener(EventDispatchingCommandBus::EVENT_COMMAND_FAILURE, function ($command, $exception) use (&$events) {
            $events[] = ['event' => EventDispatchingCommandBus::EVENT_COMMAND_FAILURE, 'arguments' => ['command' => $command, 'exception' => $exception]];
        });

        $handler = new class implements CommandHandler {
            public function handle(object $command): void { throw new Exception('boom'); }
        };
        $inner->subscribe($handler);

        $command = (object)['name' => 'fail'];

        try {
            $bus->dispatch($command);
            self::fail('Expected exception was not thrown.');
        } catch (Exception $e) {
            self::assertSame('boom', $e->getMessage());
        }

        self::assertCount(1, $events);
        self::assertSame(EventDispatchingCommandBus::EVENT_COMMAND_FAILURE, $events[0]['event']);
        self::assertSame($command, $events[0]['arguments']['command']);
        self::assertInstanceOf(Exception::class, $events[0]['arguments']['exception']);
        self::assertSame('boom', $events[0]['arguments']['exception']->getMessage());
    }
}
