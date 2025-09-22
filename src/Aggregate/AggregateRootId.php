<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Aggregate;

use Ssmiff\CqrsEs\Serializer\StringSerializable;

interface AggregateRootId extends StringSerializable
{
}
