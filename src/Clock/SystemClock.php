<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Psr\Clock\ClockInterface;

final readonly class SystemClock implements ClockInterface
{
    public function __construct(private DateTimeZone $timezone)
    {
    }

    /**
     * @throws Exception
     */
    public static function fromUTC(): self
    {
        return new self(new DateTimeZone('UTC'));
    }

    /**
     * @throws Exception
     */
    public static function fromSystemTimezone(): self
    {
        return new self(new DateTimeZone(date_default_timezone_get()));
    }

    /**
     * @throws Exception
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }
}
