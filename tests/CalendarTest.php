<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests;

use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Todo;
use Makinuk\ICalendar\Enum\Method;
use Makinuk\ICalendar\Exception\IOException;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Value\Duration;

final class CalendarTest extends TestCase
{
    public function testRendersCalendarWithEvent(): void
    {
        $calendar = new Calendar(self::event()->setSummary('Kickoff'));

        self::assertSame(self::lines(
            'BEGIN:VCALENDAR',
            'PRODID:-//makinuk//iCalendar 3.0//EN',
            'VERSION:2.0',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:event-1@example.com',
            'DTSTAMP:20260901T080000Z',
            'DTSTART:20261001T090000Z',
            'DTEND:20261001T100000Z',
            'SUMMARY:Kickoff',
            'END:VEVENT',
            'END:VCALENDAR',
        ), $calendar->render());
        self::assertSame($calendar->render(), (string) $calendar);
    }

    public function testCalendarProperties(): void
    {
        $calendar = (new Calendar())
            ->setProductId('-//Acme//Booking 1.0//EN')
            ->setMethod(Method::Request)
            ->setName('Team, Istanbul')
            ->setDescription('Shared team calendar')
            ->setRefreshInterval(Duration::hours(1))
            ->add(self::event(), Todo::create('Prepare slides'));

        $output = $calendar->render();

        self::assertStringStartsWith(self::lines(
            'BEGIN:VCALENDAR',
            'PRODID:-//Acme//Booking 1.0//EN',
            'VERSION:2.0',
            'CALSCALE:GREGORIAN',
            'METHOD:REQUEST',
            'NAME:Team\\, Istanbul',
            'X-WR-CALNAME:Team\\, Istanbul',
            'DESCRIPTION:Shared team calendar',
            'X-WR-CALDESC:Shared team calendar',
            'REFRESH-INTERVAL;VALUE=DURATION:PT1H',
            'X-PUBLISHED-TTL:PT1H',
            'BEGIN:VEVENT',
        ), $output);
        self::assertStringContainsString("BEGIN:VTODO\r\n", $output);
        self::assertCount(2, $calendar->getComponents());
        self::assertRfc5545ContentLines($output);
    }

    public function testContentType(): void
    {
        $calendar = new Calendar(self::event());

        self::assertSame('text/calendar; charset=utf-8', $calendar->getContentType());
        self::assertSame('text/calendar; charset=utf-8; method=REQUEST', $calendar->setMethod(Method::Request)->getContentType());
    }

    public function testEmptyCalendarIsInvalid(): void
    {
        $this->expectException(ValidationException::class);

        (new Calendar())->render();
    }

    public function testSave(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ics');
        self::assertIsString($path);

        try {
            $calendar = new Calendar(self::event());
            $calendar->save($path);

            self::assertSame($calendar->render(), file_get_contents($path));
        } finally {
            unlink($path);
        }
    }

    public function testSaveFailureThrows(): void
    {
        $this->expectException(IOException::class);

        (new Calendar(self::event()))->save(sys_get_temp_dir() . '/missing-dir-' . uniqid() . '/calendar.ics');
    }
}
