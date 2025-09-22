<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling;

final class SimpleCommandBus implements CommandBus
{
    private array $commandHandlers = [];
    private array$queue = [];
    private bool $isDispatching = false;

    public function subscribe(CommandHandler $handler): void
    {
        $this->commandHandlers[] = $handler;
    }

    public function dispatch(object $command): void
    {
        $this->queue[] = $command;

        if (!$this->isDispatching) {
            $this->isDispatching = true;

            try {
                while ($command = array_shift($this->queue)) {
                    foreach ($this->commandHandlers as $handler) {
                        $handler->handle($command);
                    }
                }
            } finally {
                $this->isDispatching = false;
            }
        }
    }
}
