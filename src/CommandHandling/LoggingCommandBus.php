<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling;

use Psr\Log\LoggerInterface;
use Throwable;

class LoggingCommandBus implements CommandBus
{
    private array $commandHandlers = [];

    private array $queue = [];

    private bool $isDispatching = false;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function subscribe(CommandHandler $handler): void
    {
        $this->commandHandlers[] = $handler;
    }

    /**
     * @throws Throwable
     */
    public function dispatch(object $command): void
    {
        $this->queue[] = $command;

        if (!$this->isDispatching) {
            $this->isDispatching = true;

            try {
                while ($command = array_shift($this->queue)) {
                    $this->handleHandler($command);
                }
            } finally {
                $this->isDispatching = false;
            }
        }
    }

    /**
     * @throws Throwable
     */
    private function handleHandler($command): void
    {
        foreach ($this->commandHandlers as $handler) {
            try {
                $handler->handle($command);
            } catch (Throwable $exception) {
                $this->logger->error(
                    sprintf(
                        '[Command Handler]: %s, failed with command "%s"',
                        get_class($handler),
                        $exception->getMessage()
                    ),
                    [
                        'exception' => $exception,
                    ]
                );

                throw $exception;
            }
        }
    }
}
