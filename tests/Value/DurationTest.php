<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests\Value;

use DateInterval;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Tests\TestCase;
use Makinuk\ICalendar\Value\Duration;
use PHPUnit\Framework\Attributes\DataProvider;

final class DurationTest extends TestCase
{
    /**
     * @return iterable<string, array{Duration, string}>
     */
    public static function durationProvider(): iterable
    {
        yield 'zero' => [Duration::of(), 'PT0S'];
        yield 'minutes' => [Duration::minutes(15), 'PT15M'];
        yield 'negative minutes' => [Duration::minutes(15)->negate(), '-PT15M'];
        yield 'hours and minutes' => [Duration::of(hours: 1, minutes: 30), 'PT1H30M'];
        yield 'hours and seconds need 0M' => [Duration::of(hours: 1, seconds: 5), 'PT1H0M5S'];
        yield 'days' => [Duration::days(2), 'P2D'];
        yield 'days and time' => [Duration::of(days: 1, hours: 2), 'P1DT2H'];
        yield 'weeks alone' => [Duration::weeks(3), 'P3W'];
        yield 'weeks with days become days' => [Duration::of(weeks: 1, days: 2), 'P9D'];
    }

    #[DataProvider('durationProvider')]
    public function testToString(Duration $duration, string $expected): void
    {
        self::assertSame($expected, $duration->toString());
        self::assertSame($expected, (string) $duration);
    }

    public function testFromDateInterval(): void
    {
        $interval = new DateInterval('P1DT2H3M4S');
        $interval->invert = 1;

        self::assertSame('-P1DT2H3M4S', Duration::fromDateInterval($interval)->toString());
    }

    public function testFromDateIntervalRejectsMonths(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Duration::fromDateInterval(new DateInterval('P1M'));
    }

    public function testRejectsNegativeParts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Duration::minutes(-5);
    }
}
