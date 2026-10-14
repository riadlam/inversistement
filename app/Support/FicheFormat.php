<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * The handful of presentation rules the official fiche uses, kept out of the
 * Blade templates so the portrait and landscape halves format identically.
 */
final class FicheFormat
{
    /** Arabic must be pre-shaped: DomPDF places glyphs left to right. */
    public static function ar(?string $text): string
    {
        return ArabicText::forPdf($text);
    }

    /**
     * Arabic for a narrow cell, already broken into lines the renderer must
     * not re-wrap.
     *
     * @return list<string>
     */
    public static function arLines(?string $text, int $charactersPerLine = 34): array
    {
        return ArabicText::lines($text, $charactersPerLine);
    }

    public static function da(float|int|string|null $amount): string
    {
        return $amount === null ? '' : Money::da($amount);
    }

    public static function date(mixed $value, string $fallback = ''): string
    {
        if (blank($value)) {
            return $fallback;
        }

        return Carbon::parse($value)->format('d/m/Y');
    }

    /** The fiche records receptions as quarters: "1er trim 2017". */
    public static function quarter(?int $quarter, ?int $year, string $fallback = '—'): string
    {
        if (! $quarter || ! $year) {
            return $fallback;
        }

        return ($quarter === 1 ? '1er' : $quarter.'ème')." trim {$year}";
    }

    public static function ordinal(int $n): string
    {
        return $n === 1 ? '1re' : $n.'ème';
    }

    public static function trafficLightLabel(string $light): string
    {
        return match ($light) {
            'green' => 'Vert',
            'orange' => 'Orange',
            default => 'Rouge',
        };
    }

    public static function yesNo(?bool $value, string $fallback = '—'): string
    {
        return $value === null ? $fallback : ($value ? 'Oui' : 'Non');
    }
}
