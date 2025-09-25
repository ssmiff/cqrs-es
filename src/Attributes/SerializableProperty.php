<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class SerializableProperty
{
    public function __construct() {}
}
