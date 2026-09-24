<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests\Component;

use DateInterval;
use DateTimeImmutable;
use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Enum\Classification;
use Makinuk\ICalendar\Enum\EventStatus;
use Makinuk\ICalendar\Enum\ParticipationStatus;
use Makinuk\ICalendar\Enum\Role;
use Makinuk\ICalendar\Enum\Transparency;
use Makinuk\ICalendar\Enum\Weekday;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Property\Attendee;
use Makinuk\ICalendar\Property\Property;
use Makinuk\ICalendar\Tests\TestCase;
use Makinuk\ICalendar\Value\Duration;
use Makinuk\ICalendar\Value\RecurrenceRule;

final class EventTest extends TestCase
{
    public function testMinimalEvent(): void
    {
        self::assertSame(self::lines(
            'BEGIN:VEVENT',
            'UID:event-1@example.com',
            'DTSTAMP:20260901T080000Z',
            'DTSTART:20261001T090000Z',
            'DTEND:20261001T100000Z',
            'END:VEVENT',
        ), self::event()->render());
    }

    public function testFullEvent(): void
    {
        $event = self::event()
            ->setCreated(self::utc('2026-08-01 00:00:00'))
            ->setLastModified(self::utc('2026-08-15 00:00:00'))
            ->setSummary('Sprint review, Q4')
            ->setDescription("Agenda:\n1. Demo\n2. Retro")
            ->setLocation('Maslak; Istanbul')
            ->setGeo(41.1128, 29.0209)
            ->setUrl('https://example.com/meetings/1')
            ->setOrganizer('jane@example.com', 'Jane Doe')
            ->addAttendee(new Attendee('bob@example.com', 'Bob', Role::RequiredParticipant, ParticipationStatus::NeedsAction, true))
            ->addAttendee('alice@example.com')
            ->addCategory('Work', 'Sprint, Q4')
            ->setClassification(Classification::Private)
            ->setPriority(1)
            ->setSequence(2)
            ->setStatus(EventStatus::Confirmed)
            ->setTransparency(Transparency::Opaque)
            ->setColor('teal')
            ->setRecurrenceRule(RecurrenceRule::weekly()->byDay(Weekday::Thursday)->count(4))
            ->addExceptionDate(self::utc('2026-10-08 09:00:00'))
            ->addAttachment('https://example.com/agenda.pdf', 'application/pdf')
            ->addProperty(Property::text('X-MICROSOFT-CDO-BUSYSTATUS', 'BUSY'))
            ->addAlarm(Alarm::display('Sprint review')->before(minutes: 15));

        $output = $event->render();

        self::assertSame(self::lines(
            'BEGIN:VEVENT',
            'UID:event-1@example.com',
            'DTSTAMP:20260901T080000Z',
            'CREATED:20260801T000000Z',
            'LAST-MODIFIED:20260815T000000Z',
            'DTSTART:20261001T090000Z',
            'DTEND:20261001T100000Z',
            'SUMMARY:Sprint review\\, Q4',
            'DESCRIPTION:Agenda:\\n1. Demo\\n2. Retro',
            'LOCATION:Maslak\\; Istanbul',
            'GEO:41.1128;29.0209',
            'URL:https://example.com/meetings/1',
            'ORGANIZER;CN=Jane Doe:mailto:jane@example.com',
            'ATTENDEE;CN=Bob;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE:mailto',
            ' :bob@example.com',
            'ATTENDEE:mailto:alice@example.com',
            'CATEGORIES:Work,Sprint\\, Q4',
            'CLASS:PRIVATE',
            'PRIORITY:1',
            'SEQUENCE:2',
            'STATUS:CONFIRMED',
            'TRANSP:OPAQUE',
            'COLOR:teal',
            'RRULE:FREQ=WEEKLY;COUNT=4;BYDAY=TH',
            'EXDATE:20261008T090000Z',
            'ATTACH;FMTTYPE=application/pdf:https://example.com/agenda.pdf',
            'X-MICROSOFT-CDO-BUSYSTATUS:BUSY',
            'BEGIN:VALARM',
            'ACTION:DISPLAY',
            'TRIGGER:-PT15M',
            'DESCRIPTION:Sprint review',
            'END:VALARM',
            'END:VEVENT',
        ), $output);
        self::assertRfc5545ContentLines($output);
    }

    public function testCreateShortcut(): void
    {
        $event = Event::create('Lunch', '2026-10-01 12:00', '2026-10-01 13:00');

        self::assertSame('Lunch', $event->getSummary());
        self::assertEquals(new DateTimeImmutable('2026-10-01 12:00'), $event->getStart());
        self::assertEquals(new DateTimeImmutable('2026-10-01 13:00'), $event->getEnd());
    }

    public function testGeneratesUuidAndTimestamp(): void
    {
        $event = new Event();

        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $event->getUid());
        self::assertNotSame($event->getUid(), (new Event())->getUid());
        self::assertEqualsWithDelta(time(), $event->getTimestamp()->getTimestamp(), 5);
    }

    public function testAcceptsUnixTimestamps(): void
    {
        $event = self::event()->setStart(1790841600)->setEnd(1790845200);

        self::assertStringContainsString("DTSTART:20261001T080000Z\r\nDTEND:20261001T090000Z\r\n", $event->render());
    }

    public function testAllDayEvent(): void
    {
        $event = self::event()
            ->setAllDay()
            ->setStart('2026-10-29')
            ->setEnd('2026-10-30')
            ->setRecurrenceRule(RecurrenceRule::yearly()->until('2030-10-29'))
            ->addExceptionDate('2027-10-29');

        $output = $event->render();

        self::assertStringContainsString("DTSTART;VALUE=DATE:20261029\r\nDTEND;VALUE=DATE:20261030\r\n", $output);
        self::assertStringContainsString("RRULE:FREQ=YEARLY;UNTIL=20301029\r\n", $output);
        self::assertStringContainsString("EXDATE;VALUE=DATE:20271029\r\n", $output);
    }

    public function testAllDayEventWithoutEndLastsOneDay(): void
    {
        $output = self::event()->setAllDay()->setStart('2026-10-29')->setEnd(null)->render();

        self::assertStringContainsString("DTSTART;VALUE=DATE:20261029\r\n", $output);
        self::assertStringNotContainsString('DTEND', $output);
    }

    public function testDurationReplacesEnd(): void
    {
        $event = self::event()->setDuration(new DateInterval('PT90M'));

        self::assertNull($event->getEnd());
        self::assertStringContainsString("DTSTART:20261001T090000Z\r\nDURATION:PT90M\r\n", $event->render());

        $event->setEnd(self::utc('2026-10-01 11:00'));
        self::assertNull($event->getDuration());
    }

    public function testRequiresStart(): void
    {
        $this->expectException(ValidationException::class);

        (new Event())->render();
    }

    public function testRejectsEndBeforeStart(): void
    {
        $this->expectException(ValidationException::class);

        self::event()->setEnd(self::utc('2026-10-01 08:00:00'))->render();
    }

    public function testRejectsAllDayEndOnStartDate(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('exclusive');

        self::event()->setAllDay()->setStart('2026-10-29')->setEnd('2026-10-29')->render();
    }

    public function testRejectsNegativeDuration(): void
    {
        $this->expectException(ValidationException::class);

        self::event()->setDuration(Duration::hours(1)->negate())->render();
    }

    public function testValidatesRanges(): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::event()->setPriority(10);
    }
}
