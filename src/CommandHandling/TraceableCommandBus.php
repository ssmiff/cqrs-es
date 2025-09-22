<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling;

final class TraceableCommandBus implements CommandBus
{
    private array $commands = [];
    private bool $isRecording = false;

    public function subscribe(CommandHandler $handler): void
    {
    }

    public function dispatch($command): void
    {
        if (!$this->isRecording) {
            return;
        }

        $this->commands[] = $command;
    }

    public function getRecordedCommands(): array
    {
        return $this->commands;
    }

    public function startRecording(): bool
    {
        return $this->isRecording = true;
    }
}
