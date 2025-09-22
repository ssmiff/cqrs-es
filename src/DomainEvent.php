<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs;

use Exception;
use Psr\Clock\ClockInterface;
use Ssmiff\CqrsEs\Aggregate\AggregateRootId;
use Ssmiff\CqrsEs\Clock\SystemClock;

final class DomainEvent
{
    private static ?ClockInterface $now = null;

    public function __construct(
        private readonly AggregateRootId $aggregateId,
        private readonly int $version,
        private readonly ClockInterface $recordedOn,
        private readonly object $payload,
        private Metadata $metadata,
    ) {}

    public static function setTestNow(ClockInterface $now): void
    {
        self::$now = $now;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getAggregateId(): AggregateRootId
    {
        return $this->aggregateId;
    }

    public function getPayload(): object
    {
        return $this->payload;
    }

    public function getMetaData(): Metadata
    {
        return $this->metadata;
    }

    public function getRecordedOn(): ClockInterface
    {
        return $this->recordedOn;
    }

    /**
     * @throws Exception
     */
    public static function recordNow(
        AggregateRootId $id,
        int $version,
        object $payload,
        Metadata $metadata = new Metadata(),
    ): self {
        return new self(
            $id,
            $version,
            self::$now ?? SystemClock::fromSystemTimezone(),
            $payload,
            $metadata,
        );
    }

    public function withSingleMeta(string $key, int|string|array|bool|float $value): self
    {
        $clone = clone $this;
        $clone->metadata = $this->metadata->with($key, $value);
        return $clone;
    }

    public function withMeta(Metadata $metadata): self
    {
        $clone = clone $this;
        $clone->metadata = $this->metadata->merge($metadata);
        return $clone;
    }
}
