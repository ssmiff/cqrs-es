<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling;

interface CommandHandler
{
    public function handle(object $command): void;
}
