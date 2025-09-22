<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling;

use Ssmiff\CqrsEs\HandleMethodInflector\HandleMethodInflector;
use Ssmiff\CqrsEs\HandleMethodInflector\InflectHandlerMethodsFromAttribute;

abstract class SimpleCommandHandler implements CommandHandler
{
    protected ?HandleMethodInflector $handleMethodInflector = null;

    public function handle(object $command): void
    {
        $methods = $this->getHandleMethodInflector()->handleMethods($this, $command);

        foreach ($methods as $method) {
            if (method_exists($this, $method)) {
                $this->$method($command);
            }
        }
    }

    private function getHandleMethodInflector(): HandleMethodInflector
    {
        return $this->handleMethodInflector ?? new InflectHandlerMethodsFromAttribute();
    }
}
