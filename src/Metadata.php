<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs;

use Ssmiff\CqrsEs\Serializer\Serializable;

readonly class Metadata implements Serializable
{
    public function __construct(private array $data = [])
    {
    }

    public static function from(string $key, int|string|array|bool|float $value): self
    {
        return new self([$key => $value]);
    }

    public function with(string $key, int|string|array|bool|float $value): self
    {
        return new self(array_merge($this->data, [$key => $value]));
    }

    public function get(string $key): int|string|array|null|bool|float
    {
        return $this->data[$key] ?? null;
    }

    public function merge(self $metadata): self
    {
        return new self(array_merge($this->all(), $metadata->all()));
    }

    public function all(): array
    {
        return $this->data;
    }

    public function serialize(): array
    {
        return $this->all();
    }

    public static function deserialize(array $data): static
    {
        return new static($data);
    }
}
