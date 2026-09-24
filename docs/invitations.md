# Meeting invitations

An `.ics` attached to an e-mail becomes an interactive invitation (with *Accept / Decline* buttons in
Outlook, Gmail and Apple Mail) when it follows iTIP (RFC 5546):

1. the calendar has a `METHOD`, usually `REQUEST`
2. the event has an `ORGANIZER` and one or more `ATTENDEE`s
3. the `UID` stays the same for every update of the meeting, and `SEQUENCE` increases

## Sending an invitation

```php
use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Enum\Method;
use Makinuk\ICalendar\Enum\ParticipationStatus;
use Makinuk\ICalendar\Enum\Role;
use Makinuk\ICalendar\Property\Attendee;

$event = (new Event('meeting-' . $meeting->id . '@example.com'))
    ->setSequence($meeting->revision)                   // 0 for the first invitation
    ->setSummary('Quarterly planning')
    ->setStart('2026-10-05 10:00 Europe/Istanbul')
    ->setEnd('2026-10-05 11:30 Europe/Istanbul')
    ->setLocation('https://meet.example.com/planning')
    ->setOrganizer('jane@example.com', 'Jane Doe')
    ->addAttendee(new Attendee('bob@example.com', 'Bob Smith', Role::RequiredParticipant, ParticipationStatus::NeedsAction, rsvp: true))
    ->addAttendee(new Attendee('alice@example.com', 'Alice Lee', Role::OptionalParticipant, rsvp: true));

$calendar = (new Calendar($event))->setMethod(Method::Request);
```

The organizer should be the address the e-mail is sent from, otherwise some clients show a warning.

## Attendees

```php
new Attendee(
    email: 'bob@example.com',
    name: 'Bob Smith',
    role: Role::RequiredParticipant,         // Chair, RequiredParticipant, OptionalParticipant, NonParticipant
    status: ParticipationStatus::NeedsAction, // NeedsAction, Accepted, Declined, Tentative, Delegated...
    rsvp: true,                              // ask for a reply
);

$event->addAttendee('carol@example.com', 'Carol');   // shortcut without role or status
```

## Updating and cancelling

| Action | UID | SEQUENCE | Event status | Calendar method |
|---|---|---|---|---|
| Invite | new | `0` | `Confirmed` (optional) | `Method::Request` |
| Reschedule or change | same | previous + 1 | `Confirmed` | `Method::Request` |
| Cancel | same | previous + 1 | `EventStatus::Cancelled` | `Method::Cancel` |

```php
$event->setSequence(2)->setStatus(EventStatus::Cancelled);
$calendar = (new Calendar($event))->setMethod(Method::Cancel);
```

## Sending by e-mail

`$calendar->getContentType()` returns `text/calendar; charset=utf-8; method=REQUEST`.

**Symfony Mailer**

```php
use Symfony\Component\Mime\Email;

$email = (new Email())
    ->from('jane@example.com')
    ->to('bob@example.com')
    ->subject('Invitation: Quarterly planning')
    ->text('You are invited to the quarterly planning.')
    ->attach($calendar->render(), 'invite.ics', 'text/calendar');
```

**Laravel**

```php
use Illuminate\Mail\Mailables\Attachment;

public function attachments(): array
{
    return [
        Attachment::fromData(fn () => $this->calendar->render(), 'invite.ics')
            ->withMime('text/calendar'),
    ];
}
```

**PHPMailer** adds the calendar as an inline alternative part, which gives the best result in Outlook:

```php
$mail->Body = 'You are invited to the quarterly planning.';
$mail->Ical = $calendar->render();
```

## Tips

- Test with the clients your users have: Outlook, Gmail and Apple Mail each have their own quirks.
- Do not send `METHOD:REQUEST` for a plain "add to calendar" download: use no method (a normal
  download) or `Method::Publish`.
- Send updates to every attendee, not only to the ones who changed.
