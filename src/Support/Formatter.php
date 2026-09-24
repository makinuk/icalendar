<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Support;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Makinuk\ICalendar\Exception\InvalidArgumentException;

/**
 * Low level helpers that turn values into RFC 5545 content lines.
 *
 * @internal
 */
final class Formatter
{
    public const CRLF = "\r\n";

    /** Maximum length of a content line in octets, excluding the line break (RFC 5545 §3.1). */
    public const LINE_LIMIT = 75;

    /**
     * Escapes a TEXT value (RFC 5545 §3.3.11).
     */
    public static function escapeText(string $value): string
    {
        $value = self::sanitize($value);

        return strtr($value, ['\\' => '\\\\', ';' => '\;', ',' => '\\,', "\n" => '\\n']);
    }

    /**
     * Encodes a parameter value, quoting it when needed (RFC 5545 §3.2, RFC 6868).
     */
    public static function formatParameterValue(string $value): string
    {
        $value = strtr(self::sanitize($value), ['^' => '^^', "\n" => '^n', '"' => "^'"]);

        if (preg_match('/[;:,]/', $value) === 1) {
            return '"' . $value . '"';
        }

        return $value;
    }

    /**
     * Folds a content line so that no physical line exceeds 75 octets (RFC 5545 §3.1).
     *
     * Multi-byte UTF-8 sequences are never split.
     */
    public static function fold(string $line): string
    {
        if (strlen($line) <= self::LINE_LIMIT) {
            return $line;
        }

        $characters = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY);
        if ($characters === false) {
            throw new InvalidArgumentException('Content lines must be valid UTF-8.');
        }

        $lines = [];
        $current = '';
        foreach ($characters as $character) {
            if (strlen($current) + strlen($character) > self::LINE_LIMIT) {
                $lines[] = $current;
                $current = ' ';
            }
            $current .= $character;
        }
        $lines[] = $current;

        return implode(self::CRLF, $lines);
    }

    /**
     * Formats a DATE-TIME value in UTC, e.g. 20261001T090000Z.
     */
    public static function formatDateTime(DateTimeInterface $value): string
    {
        return DateTimeImmutable::createFromInterface($value)
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Ymd\THis\Z');
    }

    /**
     * Formats a DATE value in the value's own time zone, e.g. 20261001.
     */
    public static function formatDate(DateTimeInterface $value): string
    {
        return $value->format('Ymd');
    }

    /**
     * Ensures valid UTF-8, normalises line breaks to "\n" and strips control characters.
     */
    private static function sanitize(string $value): string
    {
        if (preg_match('//u', $value) !== 1) {
            throw new InvalidArgumentException('Values must be valid UTF-8.');
        }

        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return (string) preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/', '', $value);
    }
}
