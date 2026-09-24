<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests\Support;

use DateTimeImmutable;
use DateTimeZone;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Support\Formatter;
use Makinuk\ICalendar\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FormatterTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function textProvider(): iterable
    {
        yield 'plain' => ['Meeting', 'Meeting'];
        yield 'comma and semicolon' => ['A, B; C', 'A\\, B\\; C'];
        yield 'backslash' => ['C:\\temp', 'C:\\\\temp'];
        yield 'line breaks' => ["one\ntwo\r\nthree\rfour", 'one\\ntwo\\nthree\\nfour'];
        yield 'control characters are removed' => ["a\x00b\x07c\td", "abc\td"];
        yield 'colon is not escaped' => ['10:30', '10:30'];
        yield 'turkish' => ['Toplantı: Şişli, İstanbul', 'Toplantı: Şişli\\, İstanbul'];
    }

    #[DataProvider('textProvider')]
    public function testEscapeText(string $input, string $expected): void
    {
        self::assertSame($expected, Formatter::escapeText($input));
    }

    public function testEscapeTextRejectsInvalidUtf8(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Formatter::escapeText("\xFE\xFF");
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function parameterProvider(): iterable
    {
        yield 'plain' => ['John Smith', 'John Smith'];
        yield 'quoted when containing a colon' => ['Smith: John', '"Smith: John"'];
        yield 'quoted when containing a comma' => ['Smith, John', '"Smith, John"'];
        yield 'RFC 6868 double quote' => ['John "JJ" Smith', "John ^'JJ^' Smith"];
        yield 'RFC 6868 caret and newline' => ["a^b\nc", 'a^^b^nc'];
    }

    #[DataProvider('parameterProvider')]
    public function testFormatParameterValue(string $input, string $expected): void
    {
        self::assertSame($expected, Formatter::formatParameterValue($input));
    }

    public function testShortLinesAreNotFolded(): void
    {
        $line = str_repeat('a', 75);

        self::assertSame($line, Formatter::fold($line));
    }

    public function testLongLinesAreFoldedAt75Octets(): void
    {
        $folded = Formatter::fold(str_repeat('a', 200));
        $lines = explode("\r\n", $folded);

        self::assertSame([75, 75, 52], array_map(strlen(...), $lines));
        self::assertStringStartsWith(' ', $lines[1]);
        self::assertSame(str_repeat('a', 200), str_replace("\r\n ", '', $folded));
    }

    public function testFoldingNeverSplitsMultibyteCharacters(): void
    {
        $line = 'SUMMARY:' . str_repeat('ğüşıöç', 20);
        $folded = Formatter::fold($line);

        foreach (explode("\r\n", $folded) as $physicalLine) {
            self::assertLessThanOrEqual(75, strlen($physicalLine));
            self::assertSame(1, preg_match('//u', $physicalLine), 'Each physical line must be valid UTF-8.');
        }
        self::assertSame($line, str_replace("\r\n ", '', $folded));
    }

    public function testFormatDateTimeConvertsToUtc(): void
    {
        $istanbul = new DateTimeImmutable('2026-10-01 12:00:00', new DateTimeZone('Europe/Istanbul'));

        self::assertSame('20261001T090000Z', Formatter::formatDateTime($istanbul));
    }

    public function testFormatDateKeepsLocalDate(): void
    {
        $tokyo = new DateTimeImmutable('2026-10-01 01:00:00', new DateTimeZone('Asia/Tokyo'));

        self::assertSame('20261001', Formatter::formatDate($tokyo));
    }
}
