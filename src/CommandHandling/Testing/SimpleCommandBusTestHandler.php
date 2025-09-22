<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling\Testing;

use Ssmiff\CqrsEs\CommandHandling\CommandBus;
use Ssmiff\CqrsEs\CommandHandling\CommandHandler;

final class SimpleCommandBusTestHandler implements CommandHandler
{
    private bool $handled = false;

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly object $dispatchableCommand,
    ) {}

    public function handle($command): void
    {
        if (!$this->handled) {
            $this->commandBus->dispatch($this->dispatchableCommand);
            $this->handled = true;
        }
    }
}
