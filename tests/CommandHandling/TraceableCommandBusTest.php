<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\CommandHandling;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\CommandHandling\TraceableCommandBus;

#[CoversClass(TraceableCommandBus::class)]
final class TraceableCommandBusTest extends TestCase
{
    public function testItRecordsCommandsOnlyWhenRecordingStarted(): void
    {
        $bus = new TraceableCommandBus();

        // Not recording yet
        $bus->dispatch((object)['a' => 1]);
        self::assertSame([], $bus->getRecordedCommands());

        // Start recording
        self::assertTrue($bus->startRecording());

        $cmd1 = (object)['a' => 1];
        $cmd2 = (object)['b' => 2];

        $bus->dispatch($cmd1);
        $bus->dispatch($cmd2);

        self::assertSame([$cmd1, $cmd2], $bus->getRecordedCommands());
    }
}
