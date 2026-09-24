<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests;

use DateTimeImmutable;
use DateTimeZone;
use Makinuk\ICalendar\Component\Event;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Joins lines with CRLF and appends the final CRLF, as RFC 5545 requires.
     *
     * @return non-empty-string
     */
    protected static function lines(string ...$lines): string
    {
        return implode("\r\n", $lines) . "\r\n";
    }

    protected static function utc(string $time): DateTimeImmutable
    {
        return new DateTimeImmutable($time, new DateTimeZone('UTC'));
    }

    /**
     * An event with fixed UID and DTSTAMP so that its output is deterministic.
     */
    protected static function event(): Event
    {
        return (new Event('event-1@example.com'))
            ->setTimestamp(self::utc('2026-09-01 08:00:00'))
            ->setStart(self::utc('2026-10-01 09:00:00'))
            ->setEnd(self::utc('2026-10-01 10:00:00'));
    }

    protected static function assertRfc5545ContentLines(string $output): void
    {
        self::assertStringEndsWith("\r\n", $output);
        self::assertDoesNotMatchRegularExpression('/(?<!\r)\n/', $output, 'Every line must end with CRLF.');

        foreach (explode("\r\n", rtrim($output, "\r\n")) as $line) {
            self::assertLessThanOrEqual(75, strlen($line), sprintf('Line exceeds 75 octets: "%s"', $line));
        }
    }
}
