<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\CommandHandling\Testing;

use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\CommandHandling\CommandHandler;
use Ssmiff\CqrsEs\EventHandling\EventBus;
use Ssmiff\CqrsEs\EventHandling\SimpleEventBus;
use Ssmiff\CqrsEs\EventStore\EventStore;
use Ssmiff\CqrsEs\EventStore\InMemoryEventStore;
use Ssmiff\CqrsEs\EventStore\TraceableEventStore;

/**
 * Base test case that can be used to set up a command handler scenario.
 */
abstract class CommandHandlerScenarioTestCase extends TestCase
{
    protected Scenario $scenario;

    protected function setUp(): void
    {
        $this->scenario = $this->createScenario();
    }

    protected function createScenario(): Scenario
    {
        $eventStore = new TraceableEventStore(new InMemoryEventStore());
        $eventBus = new SimpleEventBus();
        $commandHandler = $this->createCommandHandler($eventStore, $eventBus);

        return new Scenario($this, $eventStore, $commandHandler);
    }

    /**
     * Create a command handler for the given scenario test case.
     */
    abstract protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler;
}
