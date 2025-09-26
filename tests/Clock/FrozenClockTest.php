<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\Tests\Clock;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ssmiff\CqrsEs\Clock\FrozenClock;

#[CoversClass(FrozenClock::class)]
final class FrozenClockTest extends TestCase
{
    #[Test]
    public function it_parses_datetime_with_literal_T_in_format(): void
    {
        $clock = FrozenClock::fromFormattedString('Y-m-dTH:i:s', '2014-03-12T14:17:19');

        $this->assertSame('2014-03-12 14:17:19', $clock->now()->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $clock->now()->getTimezone()->getName());
    }

    #[Test]
    public function it_falls_back_to_parsing_iso_string_when_format_fails(): void
    {
        // Provide an incompatible format to force fallback path
        $clock = FrozenClock::fromFormattedString('d/m/Y H:i', '2014-03-12T14:17:19+00:00');

        $this->assertSame('2014-03-12 14:17:19', $clock->now()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_can_set_and_adjust_time(): void
    {
        $clock = FrozenClock::fromFormattedString('Y-m-d H:i:s', '2020-01-01 00:00:00');

        $clock->adjustTime('+1 day');
        $this->assertSame('2020-01-02 00:00:00', $clock->now()->format('Y-m-d H:i:s'));

        $clock->adjustTime('+2 hours');
        $this->assertSame('2020-01-02 02:00:00', $clock->now()->format('Y-m-d H:i:s'));

        // setTo resets time
        $newClock = FrozenClock::fromFormattedString('Y-m-d H:i:s', '1999-12-31 23:59:59');
        $clock->setTo($newClock->now());
        $this->assertSame('1999-12-31 23:59:59', $clock->now()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_can_create_from_utc_now(): void
    {
        $clock = FrozenClock::fromUTC();
        // We cannot assert exact time, but we can assert timezone is UTC by checking timezone name
        $this->assertSame('UTC', $clock->now()->getTimezone()->getName());
    }
}
