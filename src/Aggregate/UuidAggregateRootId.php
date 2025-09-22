<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Aggregate;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly class UuidAggregateRootId implements AggregateRootId
{
    private function __construct(private UuidInterface $uuid) {}

    public static function new(): static
    {
        return new static(Uuid::uuid7());
    }

    public static function fromString(string $id): static
    {
        return new static(Uuid::fromString($id));
    }

    public function __toString(): string
    {
        return (string)$this->uuid;
    }

    public function isEqualTo(self $uuid): bool
    {
        return $uuid->uuid->equals($this->uuid);
    }
}
