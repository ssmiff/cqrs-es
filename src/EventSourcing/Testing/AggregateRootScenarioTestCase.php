<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) 2020 Broadway project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventSourcing\Testing;

use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\EventSourcing\AggregateFactory\AggregateFactory;
use Ssmiff\CqrsEs\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;

/**
 * Base test case that can be used to set up a command handler scenario.
 */
abstract class AggregateRootScenarioTestCase extends TestCase
{
    protected Scenario $scenario;

    protected function setUp(): void
    {
        $this->scenario = $this->createScenario();
    }

    protected function createScenario(): Scenario
    {
        $aggregateRootClass = $this->getAggregateRootClass();
        $factory = $this->getAggregateRootFactory();

        return new Scenario($this, $factory, $aggregateRootClass);
    }

    abstract protected function getAggregateRootClass(): string;

    protected function getAggregateRootFactory(): AggregateFactory
    {
        return new PublicConstructorAggregateFactory();
    }
}
