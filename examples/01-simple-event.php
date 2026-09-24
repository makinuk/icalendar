<?php

declare(strict_types=1);

/*
 * The smallest useful calendar: one event with a reminder, saved to a file.
 *
 *     php examples/01-simple-event.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Component\Event;

$event = Event::create('Product demo', '+1 day 14:00', '+1 day 15:00')
    ->setDescription("We will walk through the new dashboard.\nQuestions are welcome.")
    ->setLocation('Meeting room 3, Istanbul')
    ->setOrganizer('jane@example.com', 'Jane Doe')
    ->addAlarm(Alarm::display('Product demo starts in 10 minutes')->before(minutes: 10));

$calendar = new Calendar($event);
$calendar->save(__DIR__ . '/simple-event.ics');

echo $calendar->render();
