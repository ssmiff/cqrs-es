<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling;

use Exception;
use Ssmiff\CqrsEs\EventDispatcher\EventDispatcher;

class EventDispatchingCommandBus implements CommandBus
{
    public const string EVENT_COMMAND_SUCCESS = 'ssmiff.command_handling.command_success';
    public const string EVENT_COMMAND_FAILURE = 'ssmiff.command_handling.command_failure';

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly EventDispatcher $dispatcher,
    ) {}

    /**
     * @throws Exception
     */
    public function dispatch($command): void
    {
        try {
            $this->commandBus->dispatch($command);
            $this->dispatcher->dispatch(self::EVENT_COMMAND_SUCCESS, ['command' => $command]);
        } catch (Exception $exception) {
            $this->dispatcher->dispatch(
                self::EVENT_COMMAND_FAILURE,
                ['command' => $command, 'exception' => $exception],
            );

            throw $exception;
        }
    }

    public function subscribe(CommandHandler $handler): void
    {
        $this->commandBus->subscribe($handler);
    }
}
