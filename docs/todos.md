# To-dos

`Makinuk\ICalendar\Component\Todo` represents a `VTODO` (RFC 5545 §3.6.2): a task that can be
imported into Apple Reminders, Thunderbird, Outlook Tasks and other task managers.

```php
use Makinuk\ICalendar\Component\Todo;
use Makinuk\ICalendar\Enum\TodoStatus;

$todo = Todo::create('Send the quarterly report', '2026-10-15 17:00')   // summary and optional due date
    ->setDescription('Include the revenue breakdown per region.')
    ->setPriority(1)
    ->setStatus(TodoStatus::InProcess)
    ->setPercentComplete(40);
```

A to-do supports every shared property listed for [events](events.md#properties) (summary,
description, organizer, attendees, categories, alarms, recurrence...), plus:

| Method | Property | Notes |
|---|---|---|
| `setStart(date)` | `DTSTART` | optional for to-dos |
| `setDue(date)` | `DUE` | clears the duration |
| `setDuration(duration)` | `DURATION` | clears the due date, requires a start |
| `setCompleted(date)` | `COMPLETED` | |
| `setPercentComplete(int)` | `PERCENT-COMPLETE` | 0–100 |
| `setStatus(TodoStatus)` | `STATUS` | `NeedsAction`, `InProcess`, `Completed`, `Cancelled` |
| `markCompleted(date = now)` | | sets status, 100 % and the completion time |

```php
$todo->markCompleted();                          // done now
$todo->markCompleted('2026-10-14 10:00');        // done at a given time

// A due date without a time
$todo->setAllDay()->setDue('2026-10-15');

// A reminder one hour before the due date
$todo->addAlarm(Alarm::display('Report due')->before(hours: 1)->relativeToEnd());
```

## Validation

`render()` throws a `ValidationException` when the to-do has a duration but no start, or when it is
due before it starts.
