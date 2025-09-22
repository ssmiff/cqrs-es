<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Aggregate\Exception;

use Ssmiff\CqrsEs\Exception\RuntimeException;

abstract class AggregateException extends RuntimeException {}
