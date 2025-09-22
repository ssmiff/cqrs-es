<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Stubs;

use Ssmiff\CqrsEs\Serializer\Serializable;

class SerializableClass implements Serializable
{
    public function serialize(): array
    {
        return ['key' => 'value'];
    }

    public static function deserialize(array $data): static
    {
        return new self();
    }
}
