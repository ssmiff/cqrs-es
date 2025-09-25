<?php

declare(strict_types=1);

namespace Ssmiff\CqrsEs\EventHandling;

final class ReactorsEnabled
{
    private static bool $isEnabled = true;

    public static function isEnabled(): bool
    {
        return self::$isEnabled;
    }

    public static function setEnabled(bool $isEnabled): void
    {
        self::$isEnabled = $isEnabled;
    }

    public static function withDisabled(callable $callback): void
    {
        $original = self::$isEnabled;
        self::$isEnabled = false;

        try {
            $callback();
        } finally {
            self::$isEnabled = $original;
        }
    }
}
