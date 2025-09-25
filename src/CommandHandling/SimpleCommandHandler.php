<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling;

use Ssmiff\CqrsEs\MethodInflector\MethodInflector;
use Ssmiff\CqrsEs\MethodInflector\InflectMethodsFromAttribute;

abstract class SimpleCommandHandler implements CommandHandler
{
    protected ?MethodInflector $handleMethodInflector = null;

    public function handle(object $command): void
    {
        $methods = $this->getHandleMethodInflector()->handleMethods($this, $command);

        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                $this->$method($command);
            }
        }
    }

    private function getHandleMethodInflector(): MethodInflector
    {
        return $this->handleMethodInflector ?? new InflectMethodsFromAttribute();
    }
}
