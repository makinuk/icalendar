<?php

declare(strict_types=1);

/*
 * A meeting invitation (METHOD:REQUEST) that Outlook, Gmail and Apple Mail show
 * with Accept / Decline buttons when it is e-mailed as text/calendar.
 *
 *     php examples/02-meeting-invitation.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Enum\EventStatus;
use Makinuk\ICalendar\Enum\Method;
use Makinuk\ICalendar\Enum\ParticipationStatus;
use Makinuk\ICalendar\Enum\Role;
use Makinuk\ICalendar\Property\Attendee;

// Keep the UID stable and increase the sequence when you send an update of the same meeting.
$event = (new Event('quarterly-planning-2026-q4@example.com'))
    ->setSequence(0)
    ->setSummary('Quarterly planning')
    ->setStart('2026-10-05 10:00 Europe/Istanbul')
    ->setEnd('2026-10-05 11:30 Europe/Istanbul')
    ->setLocation('https://meet.example.com/planning')
    ->setStatus(EventStatus::Confirmed)
    ->setOrganizer('jane@example.com', 'Jane Doe')
    ->addAttendee(new Attendee('bob@example.com', 'Bob Smith', Role::RequiredParticipant, ParticipationStatus::NeedsAction, rsvp: true))
    ->addAttendee(new Attendee('alice@example.com', 'Alice Lee', Role::OptionalParticipant, ParticipationStatus::NeedsAction, rsvp: true))
    ->addAlarm(Alarm::display('Quarterly planning')->before(minutes: 15));

$calendar = (new Calendar($event))->setMethod(Method::Request);

// With symfony/mailer:
//
//     $email = (new Symfony\Component\Mime\Email())
//         ->from('jane@example.com')
//         ->to('bob@example.com', 'alice@example.com')
//         ->subject('Invitation: Quarterly planning')
//         ->text('You are invited to the quarterly planning.')
//         ->attach($calendar->render(), 'invite.ics', 'text/calendar');
//
// See docs/invitations.md for PHPMailer and Laravel.
//
// To cancel later: same UID, SEQUENCE + 1, EventStatus::Cancelled and Method::Cancel.

echo 'Content-Type: ' . $calendar->getContentType() . PHP_EOL . PHP_EOL;
echo $calendar->render();
