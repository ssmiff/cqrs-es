<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventStore\Exception;

use Ssmiff\CqrsEs\Exception\RuntimeException;

abstract class EventStoreException extends RuntimeException {}
