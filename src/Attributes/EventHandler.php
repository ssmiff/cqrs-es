<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class EventHandler
{
    public function __construct(public string $eventClass) {}
}
