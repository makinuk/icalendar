# Getting started

## Installation

```bash
composer require makinuk/icalendar
```

Requires PHP 8.2 or newer and no extensions beyond the PHP core.

## Your first calendar

A `Calendar` holds one or more events or to-dos:

```php
use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Event;

$event = Event::create('Team lunch', '2026-10-01 12:00', '2026-10-01 13:00')
    ->setLocation('Kadıköy, Istanbul');

$calendar = new Calendar($event);
echo $calendar->render();
```

```text
BEGIN:VCALENDAR
PRODID:-//makinuk//iCalendar 3.0//EN
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
UID:3b0c9a3e-6f5e-4c1b-9d0e-2f8a7c6b5d4e
DTSTAMP:20260924T120000Z
DTSTART:20261001T090000Z
DTEND:20261001T100000Z
SUMMARY:Team lunch
LOCATION:Kadıköy\, Istanbul
END:VEVENT
END:VCALENDAR
```

Things the library takes care of for you:

- a unique `UID` (a UUID) and a `DTSTAMP` are generated when you create a component
- text is escaped (`,` `;` `\` and line breaks) and long lines are folded at 75 octets without breaking UTF-8 characters
- every line ends with CRLF, as the standard requires
- the component is validated when rendered; see [Errors](#errors)

## Dates and time zones

Every date setter accepts:

| Input | Example |
|---|---|
| `DateTimeInterface` | `new DateTimeImmutable('2026-10-01 12:00', new DateTimeZone('Europe/Istanbul'))` |
| A string understood by `DateTimeImmutable` | `'2026-10-01 12:00'`, `'+1 day 09:00'`, `'2026-10-01 12:00 Europe/Istanbul'` |
| A Unix timestamp | `1790848800` |

Strings without a time zone and timestamps use PHP's default time zone (`date_default_timezone_get()`).

Date-times are always written in **UTC** (`20261001T090000Z`). Every calendar client converts UTC to
the viewer's time zone, so the event shows at the right local time everywhere. All-day components are
written as plain dates; see [all-day events](events.md#all-day-and-multi-day-events).

> Writing local times with a `TZID` and a `VTIMEZONE` definition is on the [roadmap](rfc-compliance.md#roadmap).
> Recurring events that cross a daylight saving change keep their UTC time, which can shift the local time by an hour.

## Output

```php
$calendar->render();                    // the .ics content as a string
(string) $calendar;                     // same
$calendar->save('/path/to/file.ics');   // write a file, throws IOException on failure
$calendar->send('meeting.ics');         // plain PHP: send headers and content to the browser
$calendar->getContentType();            // "text/calendar; charset=utf-8" (+ "; method=REQUEST" when set)
```

In frameworks, return `render()` in a response object instead of using `send()`; see [integrations](integrations.md).

## Calendar properties

```php
use Makinuk\ICalendar\Enum\Method;
use Makinuk\ICalendar\Value\Duration;

$calendar = (new Calendar())
    ->setProductId('-//Acme//Booking 1.0//EN')     // identifies your application
    ->setName('Team calendar')                     // NAME + X-WR-CALNAME
    ->setDescription('Shared team events')         // DESCRIPTION + X-WR-CALDESC
    ->setRefreshInterval(Duration::hours(1))       // REFRESH-INTERVAL + X-PUBLISHED-TTL, for feeds
    ->setMethod(Method::Request)                   // only for e-mail invitations
    ->add($event, $todo);
```

## Errors

All exceptions implement `Makinuk\ICalendar\Exception\ICalendarException`:

| Exception | When |
|---|---|
| `InvalidArgumentException` | A setter receives a malformed value: invalid e-mail, priority outside 0–9, invalid UTF-8... |
| `ValidationException` | `render()` finds an incomplete or inconsistent component: event without start, end before start, empty calendar... |
| `IOException` | `save()` cannot write the file |

```php
use Makinuk\ICalendar\Exception\ICalendarException;

try {
    $calendar->save($path);
} catch (ICalendarException $e) {
    $logger->error('Could not create the calendar: ' . $e->getMessage());
}
```
