<?php

namespace App\Support;

/**
 * Money is stored in raw dinars everywhere. This is the only place that turns
 * it into text, so the official documents and the dashboards stay consistent.
 *
 * Algerian administrative convention: space as thousands separator, comma as
 * decimal separator — "160 000 000,00 DA".
 */
final class Money
{
    public const MILLION = 1_000_000;

    /** Narrow no-break space, so a number never wraps mid-value. */
    private const THOUSANDS = "\u{202F}";

    /** Raw dinars: "9 704 617,20". */
    public static function da(float|int|string|null $amount, int $decimals = 2): string
    {
        return number_format((float) $amount, $decimals, ',', self::THOUSANDS);
    }

    /** Raw dinars with the unit: "9 704 617,20 DA". */
    public static function daWithUnit(float|int|string|null $amount, int $decimals = 2): string
    {
        return self::da($amount, $decimals).' DA';
    }

    /** Dinars expressed in millions, for dashboard indicators: 160.0. */
    public static function toMillions(float|int|string|null $amount): float
    {
        return (float) $amount / self::MILLION;
    }

    /** Dinars rendered as millions with the unit: "160,00 M DA". */
    public static function millions(float|int|string|null $amount, int $decimals = 2): string
    {
        return number_format(self::toMillions($amount), $decimals, ',', self::THOUSANDS).' M DA';
    }

    /** Millions back to dinars, for reading legacy input. */
    public static function fromMillions(float|int|string|null $millions): float
    {
        return (float) $millions * self::MILLION;
    }

    /** Percentages: "63,2 %". */
    public static function pct(float|int|string|null $value, int $decimals = 1): string
    {
        return number_format((float) $value, $decimals, ',', self::THOUSANDS).' %';
    }

    /**
     * Formats a calculation-engine metric according to the unit its rule
     * declares, so one helper covers dinars, percentages, days and points.
     */
    public static function metric(float|int|string|null $value, string $unit): string
    {
        if ($value === null) {
            return '—';
        }

        return match ($unit) {
            'da' => self::daWithUnit($value),
            'pct' => self::pct($value),
            'days' => number_format((float) $value, 0, ',', self::THOUSANDS).' j',
            'points' => number_format((float) $value, 0, ',', self::THOUSANDS).' pts',
            default => number_format((float) $value, 2, ',', self::THOUSANDS),
        };
    }
}
