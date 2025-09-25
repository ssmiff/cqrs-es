<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\CommandHandling;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ssmiff\CqrsEs\CommandHandling\CommandHandler;
use Ssmiff\CqrsEs\CommandHandling\LoggingCommandBus;
use Throwable;

#[CoversClass(LoggingCommandBus::class)]
final class LoggingCommandBusTest extends TestCase
{
    public function testItLogsErrorWhenHandlerThrowsAndRethrows(): void
    {
        $logger = new class implements LoggerInterface {
            public array $errors = [];
            public function emergency(string|\Stringable $message, array $context = []): void {}
            public function alert(string|\Stringable $message, array $context = []): void {}
            public function critical(string|\Stringable $message, array $context = []): void {}
            public function error(string|\Stringable $message, array $context = []): void { $this->errors[] = [$message, $context]; }
            public function warning(string|\Stringable $message, array $context = []): void {}
            public function notice(string|\Stringable $message, array $context = []): void {}
            public function info(string|\Stringable $message, array $context = []): void {}
            public function debug(string|\Stringable $message, array $context = []): void {}
            public function log($level, string|\Stringable $message, array $context = []): void {}
        };

        $bus = new LoggingCommandBus($logger);

        $handler = new class implements CommandHandler {
            public function handle(object $command): void { throw new \RuntimeException('kaboom'); }
        };

        $bus->subscribe($handler);

        $command = (object)['cmd' => true];

        try {
            $bus->dispatch($command);
            self::fail('Expected exception was not thrown.');
        } catch (Throwable $e) {
            self::assertSame('kaboom', $e->getMessage());
        }

        self::assertCount(1, $logger->errors);
        [$message, $context] = $logger->errors[0];
        self::assertStringContainsString('[Command Handler]:', $message);
        self::assertArrayHasKey('exception', $context);
        self::assertInstanceOf(\RuntimeException::class, $context['exception']);
        self::assertSame('kaboom', $context['exception']->getMessage());
    }

    public function testItDoesNotLogErrorWhenNoException(): void
    {
        $logger = new class implements LoggerInterface {
            public array $errors = [];
            public function emergency(string|\Stringable $message, array $context = []): void {}
            public function alert(string|\Stringable $message, array $context = []): void {}
            public function critical(string|\Stringable $message, array $context = []): void {}
            public function error(string|\Stringable $message, array $context = []): void { $this->errors[] = [$message, $context]; }
            public function warning(string|\Stringable $message, array $context = []): void {}
            public function notice(string|\Stringable $message, array $context = []): void {}
            public function info(string|\Stringable $message, array $context = []): void {}
            public function debug(string|\Stringable $message, array $context = []): void {}
            public function log($level, string|\Stringable $message, array $context = []): void {}
        };

        $bus = new LoggingCommandBus($logger);

        $handled = 0;
        $handler = new class($handled) implements CommandHandler {
            public function __construct(private int &$handled) {}
            public function handle(object $command): void { $this->handled++; }
        };

        $bus->subscribe($handler);
        $bus->dispatch((object)[]);

        self::assertSame(0, count($logger->errors));
    }
}
