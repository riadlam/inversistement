<?php

namespace App\Support;

/**
 * Arabic for DomPDF.
 *
 * DomPDF lays glyphs out left to right and does no contextual shaping, so
 * Arabic handed to it straight comes out as disconnected letters in reverse
 * order. This maps each letter to its isolated / initial / medial / final
 * presentation form (Unicode block FE70–FEFC), applies the mandatory lam-alef
 * ligatures, then reverses the run so the PDF's left-to-right placement
 * produces right-to-left reading order.
 *
 * Latin words and digits inside the string keep their own direction.
 */
final class ArabicText
{
    /**
     * letter => [isolated, final, initial, medial].
     *
     * A letter with no initial form (the six non-connectors) simply repeats
     * its isolated and final forms, which is what makes the joining logic fall
     * out of a single table.
     */
    private const FORMS = [
        'ء' => ["\u{FE80}", "\u{FE80}", "\u{FE80}", "\u{FE80}"],
        'آ' => ["\u{FE81}", "\u{FE82}", "\u{FE81}", "\u{FE82}"],
        'أ' => ["\u{FE83}", "\u{FE84}", "\u{FE83}", "\u{FE84}"],
        'ؤ' => ["\u{FE85}", "\u{FE86}", "\u{FE85}", "\u{FE86}"],
        'إ' => ["\u{FE87}", "\u{FE88}", "\u{FE87}", "\u{FE88}"],
        'ئ' => ["\u{FE89}", "\u{FE8A}", "\u{FE8B}", "\u{FE8C}"],
        'ا' => ["\u{FE8D}", "\u{FE8E}", "\u{FE8D}", "\u{FE8E}"],
        'ب' => ["\u{FE8F}", "\u{FE90}", "\u{FE91}", "\u{FE92}"],
        'ة' => ["\u{FE93}", "\u{FE94}", "\u{FE93}", "\u{FE94}"],
        'ت' => ["\u{FE95}", "\u{FE96}", "\u{FE97}", "\u{FE98}"],
        'ث' => ["\u{FE99}", "\u{FE9A}", "\u{FE9B}", "\u{FE9C}"],
        'ج' => ["\u{FE9D}", "\u{FE9E}", "\u{FE9F}", "\u{FEA0}"],
        'ح' => ["\u{FEA1}", "\u{FEA2}", "\u{FEA3}", "\u{FEA4}"],
        'خ' => ["\u{FEA5}", "\u{FEA6}", "\u{FEA7}", "\u{FEA8}"],
        'د' => ["\u{FEA9}", "\u{FEAA}", "\u{FEA9}", "\u{FEAA}"],
        'ذ' => ["\u{FEAB}", "\u{FEAC}", "\u{FEAB}", "\u{FEAC}"],
        'ر' => ["\u{FEAD}", "\u{FEAE}", "\u{FEAD}", "\u{FEAE}"],
        'ز' => ["\u{FEAF}", "\u{FEB0}", "\u{FEAF}", "\u{FEB0}"],
        'س' => ["\u{FEB1}", "\u{FEB2}", "\u{FEB3}", "\u{FEB4}"],
        'ش' => ["\u{FEB5}", "\u{FEB6}", "\u{FEB7}", "\u{FEB8}"],
        'ص' => ["\u{FEB9}", "\u{FEBA}", "\u{FEBB}", "\u{FEBC}"],
        'ض' => ["\u{FEBD}", "\u{FEBE}", "\u{FEBF}", "\u{FEC0}"],
        'ط' => ["\u{FEC1}", "\u{FEC2}", "\u{FEC3}", "\u{FEC4}"],
        'ظ' => ["\u{FEC5}", "\u{FEC6}", "\u{FEC7}", "\u{FEC8}"],
        'ع' => ["\u{FEC9}", "\u{FECA}", "\u{FECB}", "\u{FECC}"],
        'غ' => ["\u{FECD}", "\u{FECE}", "\u{FECF}", "\u{FED0}"],
        'ف' => ["\u{FED1}", "\u{FED2}", "\u{FED3}", "\u{FED4}"],
        'ق' => ["\u{FED5}", "\u{FED6}", "\u{FED7}", "\u{FED8}"],
        'ك' => ["\u{FED9}", "\u{FEDA}", "\u{FEDB}", "\u{FEDC}"],
        'ل' => ["\u{FEDD}", "\u{FEDE}", "\u{FEDF}", "\u{FEE0}"],
        'م' => ["\u{FEE1}", "\u{FEE2}", "\u{FEE3}", "\u{FEE4}"],
        'ن' => ["\u{FEE5}", "\u{FEE6}", "\u{FEE7}", "\u{FEE8}"],
        'ه' => ["\u{FEE9}", "\u{FEEA}", "\u{FEEB}", "\u{FEEC}"],
        'و' => ["\u{FEED}", "\u{FEEE}", "\u{FEED}", "\u{FEEE}"],
        'ى' => ["\u{FEEF}", "\u{FEF0}", "\u{FEEF}", "\u{FEF0}"],
        'ي' => ["\u{FEF1}", "\u{FEF2}", "\u{FEF3}", "\u{FEF4}"],
        'پ' => ["\u{FB56}", "\u{FB57}", "\u{FB58}", "\u{FB59}"],
        'چ' => ["\u{FB7A}", "\u{FB7B}", "\u{FB7C}", "\u{FB7D}"],
        'ڤ' => ["\u{FB6A}", "\u{FB6B}", "\u{FB6C}", "\u{FB6D}"],
        'گ' => ["\u{FB92}", "\u{FB93}", "\u{FB94}", "\u{FB95}"],
    ];

    /** Letters that never join to the following one. */
    private const NON_JOINING = ['ء', 'آ', 'أ', 'ؤ', 'إ', 'ا', 'د', 'ذ', 'ر', 'ز', 'و', 'ة'];

    /** lam + alef variant => [isolated ligature, final ligature]. */
    private const LAM_ALEF = [
        'آ' => ["\u{FEF5}", "\u{FEF6}"],
        'أ' => ["\u{FEF7}", "\u{FEF8}"],
        'إ' => ["\u{FEF9}", "\u{FEFA}"],
        'ا' => ["\u{FEFB}", "\u{FEFC}"],
    ];

    /** Combining marks ride on the preceding letter and never break a join. */
    private const DIACRITICS = [
        "\u{064B}", "\u{064C}", "\u{064D}", "\u{064E}", "\u{064F}", "\u{0650}",
        "\u{0651}", "\u{0652}", "\u{0653}", "\u{0654}", "\u{0655}", "\u{0670}",
    ];

    /** Characters whose glyph must be mirrored when the run is reversed. */
    private const MIRRORED = [
        '(' => ')', ')' => '(',
        '[' => ']', ']' => '[',
        '{' => '}', '}' => '{',
        '<' => '>', '>' => '<',
    ];

    public static function containsArabic(?string $text): bool
    {
        return $text !== null && preg_match('/\p{Arabic}/u', $text) === 1;
    }

    /**
     * Shapes and reverses `$text` for DomPDF. Strings without Arabic are
     * returned untouched, so it is safe to call on any field.
     */
    public static function forPdf(?string $text): string
    {
        $text = (string) $text;

        if (! self::containsArabic($text)) {
            return $text;
        }

        return implode('', array_reverse(self::segments(self::shape($text))));
    }

    /**
     * Wraps `$text` before shaping and returns the lines top to bottom.
     *
     * A reversed run cannot be re-wrapped by the renderer: DomPDF would break
     * it wherever it fits and the visual line order would come out upside
     * down. So the caller emits one non-wrapping element per line, and the
     * wrapping happens here on the logical text where word order still means
     * something.
     *
     * @return list<string>
     */
    public static function lines(?string $text, int $charactersPerLine = 34): array
    {
        $text = trim((string) $text);

        if ($text === '') {
            return [];
        }

        if (! self::containsArabic($text)) {
            return [$text];
        }

        $lines = [];
        $current = '';

        foreach (preg_split('/\s+/u', $text) as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if (mb_strlen($candidate) <= $charactersPerLine || $current === '') {
                $current = $candidate;

                continue;
            }

            $lines[] = $current;
            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return array_map(self::forPdf(...), $lines);
    }

    /** Applies the contextual presentation forms, left to right. */
    private static function shape(string $text): string
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = [];
        $count = count($chars);

        for ($i = 0; $i < $count; $i++) {
            $char = $chars[$i];

            if (in_array($char, self::DIACRITICS, true)) {
                $out[] = $char;

                continue;
            }

            if (! isset(self::FORMS[$char])) {
                $out[] = $char;

                continue;
            }

            $prev = self::neighbour($chars, $i, -1);
            $next = self::neighbour($chars, $i, 1);

            // ل + ا collapses into a single glyph, consuming the alef.
            if ($char === 'ل' && $next !== null && isset(self::LAM_ALEF[$next])) {
                [$isolated, $final] = self::LAM_ALEF[$next];
                $out[] = self::joinsForward($prev) ? $final : $isolated;
                $i = self::skipTo($chars, $i, $next);

                continue;
            }

            $joinsBefore = self::joinsForward($prev);
            $joinsAfter = $next !== null && isset(self::FORMS[$next]);

            $out[] = match (true) {
                $joinsBefore && $joinsAfter => self::FORMS[$char][3],
                $joinsBefore => self::FORMS[$char][1],
                $joinsAfter => self::FORMS[$char][2],
                default => self::FORMS[$char][0],
            };
        }

        return implode('', $out);
    }

    /** The nearest non-diacritic character in `$direction`, or null. */
    private static function neighbour(array $chars, int $index, int $direction): ?string
    {
        for ($i = $index + $direction; $i >= 0 && $i < count($chars); $i += $direction) {
            if (! in_array($chars[$i], self::DIACRITICS, true)) {
                return $chars[$i];
            }
        }

        return null;
    }

    /** Whether the preceding letter connects to the current one. */
    private static function joinsForward(?string $char): bool
    {
        return $char !== null
            && isset(self::FORMS[$char])
            && ! in_array($char, self::NON_JOINING, true);
    }

    /** Advances past the alef a lam-alef ligature just consumed. */
    private static function skipTo(array $chars, int $index, string $target): int
    {
        for ($i = $index + 1; $i < count($chars); $i++) {
            if ($chars[$i] === $target) {
                return $i;
            }
        }

        return $index;
    }

    /**
     * Splits a shaped string into runs, reversing nothing inside a Latin or
     * numeric run so "60 m3" and "SARL MASWID" still read correctly once the
     * whole line is flipped.
     *
     * @return list<string>
     */
    private static function segments(string $text): array
    {
        preg_match_all('/[A-Za-z0-9][A-Za-z0-9\/.,:%\-]*|\X/u', $text, $matches);

        $segments = [];
        foreach ($matches[0] as $token) {
            $segments[] = mb_strlen($token) > 1
                ? $token
                : (self::MIRRORED[$token] ?? $token);
        }

        return $segments;
    }
}
