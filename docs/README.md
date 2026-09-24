# Documentation

1. [Getting started](getting-started.md): installation, your first calendar, dates and time zones, output
2. [Events](events.md): `VEVENT`, timed, all-day and multi-day events, every supported property
3. [To-dos](todos.md): `VTODO`, due dates, progress and completion
4. [Alarms](alarms.md): `VALARM` display, e-mail and audio reminders
5. [Recurrence](recurrence.md): `RRULE` and `EXDATE`
6. [Meeting invitations](invitations.md): `METHOD`, attendees, updates and cancellations by e-mail
7. [Integrations](integrations.md): Laravel, Symfony, plain PHP downloads and subscribable feeds
8. [Extending](extending.md): custom properties, new components, architecture overview
9. [RFC 5545 compliance](rfc-compliance.md): what is supported, what is not yet, and the roadmap

## Class overview

| Class | Purpose |
|---|---|
| `Makinuk\ICalendar\Calendar` | The `VCALENDAR` container; renders, saves and sends the file |
| `Makinuk\ICalendar\Component\Event` | An event (`VEVENT`) |
| `Makinuk\ICalendar\Component\Todo` | A task (`VTODO`) |
| `Makinuk\ICalendar\Component\Alarm` | A reminder (`VALARM`) inside an event or to-do |
| `Makinuk\ICalendar\Property\Organizer` / `Attendee` | People, addressed by e-mail |
| `Makinuk\ICalendar\Property\Property` | Any raw content line, for properties without a dedicated setter |
| `Makinuk\ICalendar\Value\Duration` | A duration such as `PT1H30M` |
| `Makinuk\ICalendar\Value\RecurrenceRule` | A recurrence rule builder |
| `Makinuk\ICalendar\Enum\*` | Allowed values: `Method`, `EventStatus`, `TodoStatus`, `Role`, `ParticipationStatus`, `Classification`, `Transparency`, `Frequency`, `Weekday` |
| `Makinuk\ICalendar\Exception\*` | `InvalidArgumentException`, `ValidationException`, `IOException`, all implementing `ICalendarException` |
