<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling;

interface CommandBus
{
    public function dispatch(object $command): void;

    public function subscribe(CommandHandler $handler): void;
}
