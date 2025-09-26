<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Stubs;

use Ssmiff\CqrsEs\Serializer\HasSecureFields;

class DecryptDto implements HasSecureFields
{
    public function __construct(public string $public, public string $secret) {}
    public static function getSecureFields(): array { return ['secret']; }
}
