<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Serializer;

interface HasSecureFields
{
    public static function getSecureFields(): array;
}
