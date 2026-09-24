# makinuk/icalendar

[![CI](https://github.com/makinuk/icalendar/actions/workflows/ci.yml/badge.svg)](https://github.com/makinuk/icalendar/actions/workflows/ci.yml)
[![Latest version](https://img.shields.io/packagist/v/makinuk/icalendar.svg)](https://packagist.org/packages/makinuk/icalendar)
[![Downloads](https://img.shields.io/packagist/dt/makinuk/icalendar.svg)](https://packagist.org/packages/makinuk/icalendar)
[![PHP](https://img.shields.io/packagist/dependency-v/makinuk/icalendar/php.svg)](https://packagist.org/packages/makinuk/icalendar)
[![License](https://img.shields.io/packagist/l/makinuk/icalendar.svg)](LICENSE)

Generate standards-compliant **iCalendar (`.ics`) files** in PHP: events, to-dos, reminders,
recurring events, calendar feeds and meeting invitations that work in Outlook, Google Calendar
and Apple Calendar.

- **RFC 5545 compliant output**: CRLF line endings, text escaping, UTF-8-safe line folding, UTC dates
- **Fluent, typed API**: enums instead of magic strings, validation with clear error messages
- **Zero dependencies**: only PHP 8.2+
- **Extensible**: add any X- or IANA property, or new component types

## Installation

```bash
composer require makinuk/icalendar
```

| Version | PHP    | Status                          |
|---------|--------|---------------------------------|
| 3.x     | ≥ 8.2  | Active development              |
| 2.x     | ≥ 7.4  | No longer maintained            |

Upgrading from 2.x? Read the [upgrade guide](UPGRADE-3.0.md).

## Quick start

```php
use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Component\Event;

$event = Event::create('Product demo', '2026-10-01 14:00', '2026-10-01 15:00')
    ->setDescription("We will walk through the new dashboard.\nQuestions are welcome.")
    ->setLocation('Meeting room 3, Istanbul')
    ->setOrganizer('jane@example.com', 'Jane Doe')
    ->addAlarm(Alarm::display('Product demo')->before(minutes: 10));

$calendar = new Calendar($event);

$calendar->save('demo.ics');        // write a file
$ics = $calendar->render();         // or get the string
$calendar->send('demo.ics');        // or download it in plain PHP
```

Dates accept a `DateTimeInterface`, any string `DateTimeImmutable` understands (`'2026-10-01 14:00'`,
`'+1 day'`, `'2026-10-01 14:00 Europe/Istanbul'`) or a Unix timestamp.

## What can I build with it?

| Use case | Example |
|---|---|
| "Add to calendar" button for bookings, orders or webinars | [01-simple-event.php](examples/01-simple-event.php) |
| Meeting invitations with Accept / Decline in the e-mail client | [02-meeting-invitation.php](examples/02-meeting-invitation.php) |
| Recurring events: stand-ups, courses, shifts | [03-recurring-events.php](examples/03-recurring-events.php) |
| Holidays, multi-day conferences and task lists | [04-all-day-and-todos.php](examples/04-all-day-and-todos.php) |
| Subscribable calendar feeds (`webcal://`) | [05-calendar-feed.php](examples/05-calendar-feed.php) |

## A few more examples

**Meeting invitation**

```php
use Makinuk\ICalendar\Enum\Method;
use Makinuk\ICalendar\Property\Attendee;

$event = Event::create('Quarterly planning', '2026-10-05 10:00', '2026-10-05 11:30')
    ->setOrganizer('jane@example.com', 'Jane Doe')
    ->addAttendee(new Attendee('bob@example.com', 'Bob Smith', rsvp: true));

$calendar = (new Calendar($event))->setMethod(Method::Request);

// Attach $calendar->render() to an e-mail with the type $calendar->getContentType().
```

**Recurring event**

```php
use Makinuk\ICalendar\Enum\Weekday;
use Makinuk\ICalendar\Value\RecurrenceRule;

Event::create('Monthly review', '2026-10-30 16:00', '2026-10-30 17:00')
    ->setRecurrenceRule(RecurrenceRule::monthly()->byDay(Weekday::Friday->nth(-1)));   // last Friday
```

**All-day event and to-do**

```php
use Makinuk\ICalendar\Component\Todo;

(new Event())->setAllDay()->setStart('2026-10-29')->setSummary('Republic Day');

Todo::create('Send the quarterly report', '2026-10-15 17:00')->setPriority(1);
```

## Documentation

Full documentation lives in the [`docs/`](docs/README.md) folder:

- [Getting started](docs/getting-started.md)
- [Events](docs/events.md), [to-dos](docs/todos.md), [alarms](docs/alarms.md) and [recurrence](docs/recurrence.md)
- [Meeting invitations by e-mail](docs/invitations.md)
- [Framework integration: Laravel, Symfony, plain PHP, feeds](docs/integrations.md)
- [Extending the library](docs/extending.md)
- [RFC 5545 support matrix and roadmap](docs/rfc-compliance.md)

## Contributing

Contributions are very welcome, from bug reports to new components. The
[roadmap](docs/rfc-compliance.md#roadmap) lists good first issues. Please read
[CONTRIBUTING.md](CONTRIBUTING.md) and our [code of conduct](CODE_OF_CONDUCT.md).

```bash
composer install
composer check   # coding style, static analysis and tests
```

Security issues: see [SECURITY.md](SECURITY.md).

## License

MIT. See [LICENSE](LICENSE).
