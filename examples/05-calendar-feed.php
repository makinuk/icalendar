<?php

declare(strict_types=1);

/*
 * A subscribable calendar feed (webcal://). Run it with the built-in server and
 * subscribe to http://localhost:8000 from Apple Calendar, Outlook or Google Calendar:
 *
 *     php -S localhost:8000 examples/05-calendar-feed.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Value\Duration;

// In a real application these rows come from your database.
$bookings = [
    ['id' => 101, 'title' => 'Haircut', 'start' => '+1 day 10:00', 'end' => '+1 day 10:30'],
    ['id' => 102, 'title' => 'Dentist', 'start' => '+3 days 15:00', 'end' => '+3 days 16:00'],
];

$calendar = (new Calendar())
    ->setProductId('-//Example//Booking Feed 1.0//EN')
    ->setName('My bookings')
    ->setRefreshInterval(Duration::hours(1));

foreach ($bookings as $booking) {
    // A UID derived from your primary key keeps the event stable across refreshes.
    $calendar->add(
        (new Event('booking-' . $booking['id'] . '@example.com'))
            ->setSummary($booking['title'])
            ->setStart($booking['start'])
            ->setEnd($booking['end']),
    );
}

$calendar->send('bookings.ics', asAttachment: false);
