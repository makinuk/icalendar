<?php

declare(strict_types=1);

/*
 * All-day events, multi-day events and to-dos.
 *
 *     php examples/04-all-day-and-todos.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Component\Todo;
use Makinuk\ICalendar\Enum\Transparency;
use Makinuk\ICalendar\Value\RecurrenceRule;

// A single all-day event: no end date needed.
$holiday = (new Event())
    ->setAllDay()
    ->setStart('2026-10-29')
    ->setSummary('Republic Day')
    ->setTransparency(Transparency::Transparent)
    ->setRecurrenceRule(RecurrenceRule::yearly());

// A three-day conference: the end date of an all-day event is exclusive.
$conference = (new Event())
    ->setAllDay()
    ->setStart('2026-11-10')
    ->setEnd('2026-11-13')
    ->setSummary('PHP Conference')
    ->setLocation('Istanbul Congress Center')
    ->setGeo(41.0451, 28.9869);

$report = Todo::create('Send the quarterly report', '2026-10-15 17:00')
    ->setPriority(1)
    ->addAlarm(Alarm::display('The quarterly report is due in one hour')->before(hours: 1)->relativeToEnd());

$done = Todo::create('Book the conference hotel')->markCompleted();

echo (new Calendar($holiday, $conference, $report, $done))->setName('Team calendar')->render();
