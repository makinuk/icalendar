<?php

declare(strict_types=1);

/*
 * Recurring events with RRULE and EXDATE.
 *
 *     php examples/03-recurring-events.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Enum\Weekday;
use Makinuk\ICalendar\Value\RecurrenceRule;

// Every weekday at 09:30 for 20 occurrences, skipping 29 October (a public holiday).
$standup = Event::create('Daily stand-up', '2026-10-01 09:30', '2026-10-01 09:45')
    ->setRecurrenceRule(
        RecurrenceRule::weekly()
            ->byDay(Weekday::Monday, Weekday::Tuesday, Weekday::Wednesday, Weekday::Thursday, Weekday::Friday)
            ->count(20),
    )
    ->addExceptionDate('2026-10-29 09:30');

// Last Friday of every month until the end of 2027.
$review = Event::create('Monthly review', '2026-10-30 16:00', '2026-10-30 17:00')
    ->setRecurrenceRule(RecurrenceRule::monthly()->byDay(Weekday::Friday->nth(-1))->until('2027-12-31 23:59'));

// Every other week on Tuesday and Thursday.
$pairing = Event::create('Pair programming', '2026-10-06 13:00', '2026-10-06 15:00')
    ->setRecurrenceRule(RecurrenceRule::weekly()->interval(2)->byDay(Weekday::Tuesday, Weekday::Thursday));

echo (new Calendar($standup, $review, $pairing))->render();
