<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests\Value;

use Makinuk\ICalendar\Enum\Frequency;
use Makinuk\ICalendar\Enum\Weekday;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Tests\TestCase;
use Makinuk\ICalendar\Value\RecurrenceRule;

final class RecurrenceRuleTest extends TestCase
{
    public function testWeeklyOnSelectedDays(): void
    {
        $rule = RecurrenceRule::weekly()->interval(2)->byDay(Weekday::Monday, Weekday::Wednesday)->count(10);

        self::assertSame('FREQ=WEEKLY;COUNT=10;INTERVAL=2;BYDAY=MO,WE', $rule->toString());
    }

    public function testLastFridayOfTheMonth(): void
    {
        self::assertSame('FREQ=MONTHLY;BYDAY=-1FR', RecurrenceRule::monthly()->byDay(Weekday::Friday->nth(-1))->toString());
    }

    public function testUntilIsWrittenInUtcOrAsDate(): void
    {
        $rule = RecurrenceRule::daily()->until(self::utc('2026-12-31 23:00:00'));

        self::assertSame('FREQ=DAILY;UNTIL=20261231T230000Z', $rule->toString());
        self::assertSame('FREQ=DAILY;UNTIL=20261231', $rule->toString(dateOnly: true));
    }

    public function testAllByRulesAndWeekStart(): void
    {
        $rule = (new RecurrenceRule(Frequency::Yearly))
            ->byMonth(1, 6)
            ->byMonthDay(1, -1)
            ->byYearDay(100)
            ->byWeekNumber(20)
            ->byHour(9)
            ->byMinute(30)
            ->bySecond(0)
            ->bySetPosition(-1)
            ->byDay('1mo')
            ->weekStart(Weekday::Sunday);

        self::assertSame(
            'FREQ=YEARLY;BYMONTH=1,6;BYMONTHDAY=1,-1;BYYEARDAY=100;BYWEEKNO=20;BYHOUR=9;BYMINUTE=30;BYSECOND=0;BYSETPOS=-1;BYDAY=1MO;WKST=SU',
            $rule->toString(),
        );
    }

    public function testCountAndUntilAreExclusive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RecurrenceRule::daily()->count(3)->until('2026-12-31');
    }

    public function testRejectsInvalidByDay(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RecurrenceRule::weekly()->byDay('XX');
    }

    public function testRejectsOutOfRangeValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RecurrenceRule::yearly()->byMonth(13);
    }
}
