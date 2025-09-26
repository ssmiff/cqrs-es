<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Clock;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Psr\Clock\ClockInterface;

final class FrozenClock implements ClockInterface
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    public static function fromFormattedString(string $format, string $datetime): self
    {
        // Try provided format first
        $dt = DateTimeImmutable::createFromFormat($format, $datetime, new DateTimeZone('UTC'));

        // If parsing failed (e.g., literal 'T' not escaped), try escaping 'T' characters
        if ($dt === false) {
            $escapedFormat = str_replace('T', '\\T', $format);
            $dt = DateTimeImmutable::createFromFormat($escapedFormat, $datetime, new DateTimeZone('UTC'));
        }

        // As a final fallback, try constructing from the datetime string directly (ISO 8601 etc.)
        if ($dt === false) {
            $dt = new DateTimeImmutable($datetime, new DateTimeZone('UTC'));
        }

        return new self($dt);
    }

    /**
     * @throws Exception
     */
    public static function fromUTC(): self
    {
        return new self(new DateTimeImmutable('now', new DateTimeZone('UTC')));
    }

    public function setTo(DateTimeImmutable $now): void
    {
        $this->now = $now;
    }

    /**
     * Adjusts the current time by a given modifier.
     *
     * @param string $modifier @see https://www.php.net/manual/en/datetime.formats.php
     *
     * @throws DateMalformedStringException
     */
    public function adjustTime(string $modifier): void
    {
        $this->now = $this->now->modify($modifier);
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
