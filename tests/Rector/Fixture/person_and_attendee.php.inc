<?php

namespace App\Fixture;

use makinuk\ICalendar\ICalAttendee;
use makinuk\ICalendar\ICalEvent;
use makinuk\ICalendar\ICalPerson;

function people(ICalEvent $event, ICalPerson $person, bool $reply): string
{
    $organizer = new ICalPerson('Jane Doe', 'jane@example.com');

    $event->setOrganizer($organizer)
        ->addAttendee(new ICalAttendee(new ICalPerson('Bob', 'bob@example.com'), true))
        ->addAttendee(new ICalAttendee(new ICalPerson('Alice', 'alice@example.com')))
        ->addAttendee(new ICalAttendee($person, $reply))
        ->addAttendee(new ICalAttendee($person, false));

    $person->Name = 'Changed';

    return $organizer->Name . ' <' . $person->Email . '>';
}

?>
-----
<?php

namespace App\Fixture;

use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Property\Organizer;
use Makinuk\ICalendar\Property\Attendee;

function people(Event $event, Organizer $person, bool $reply): string
{
    $organizer = new Organizer('jane@example.com', 'Jane Doe');

    $event->setOrganizer($organizer)
        ->addAttendee(new Attendee('bob@example.com', 'Bob', rsvp: true))
        ->addAttendee(new Attendee('alice@example.com', 'Alice'))
        ->addAttendee(new Attendee($person->getEmail(), $person->getName(), rsvp: $reply))
        ->addAttendee(new Attendee($person->getEmail(), $person->getName()));

    $person->Name = 'Changed';

    return $organizer->getName() . ' <' . $person->getEmail() . '>';
}

?>
