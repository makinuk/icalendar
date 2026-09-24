# Recurrence

`Makinuk\ICalendar\Value\RecurrenceRule` builds an `RRULE` (RFC 5545 §3.3.10). The occurrences are
computed by the calendar client; this library only writes the rule.

```php
use Makinuk\ICalendar\Enum\Weekday;
use Makinuk\ICalendar\Value\RecurrenceRule;

$event = Event::create('Daily stand-up', '2026-10-01 09:30', '2026-10-01 09:45')
    ->setRecurrenceRule(RecurrenceRule::daily()->count(10));
```

The start of the event is the first occurrence and defines the time of day of all occurrences.

## Common rules

| Need | Rule |
|---|---|
| Every day, 10 times | `RecurrenceRule::daily()->count(10)` |
| Every weekday | `RecurrenceRule::weekly()->byDay(Weekday::Monday, Weekday::Tuesday, Weekday::Wednesday, Weekday::Thursday, Weekday::Friday)` |
| Every other week on Tuesday and Thursday | `RecurrenceRule::weekly()->interval(2)->byDay(Weekday::Tuesday, Weekday::Thursday)` |
| On the 1st and 15th of every month | `RecurrenceRule::monthly()->byMonthDay(1, 15)` |
| On the last day of every month | `RecurrenceRule::monthly()->byMonthDay(-1)` |
| On the second Monday of every month | `RecurrenceRule::monthly()->byDay(Weekday::Monday->nth(2))` |
| On the last Friday of every month | `RecurrenceRule::monthly()->byDay(Weekday::Friday->nth(-1))` |
| On the last working day of every month | `RecurrenceRule::monthly()->byDay(Weekday::Monday, Weekday::Tuesday, Weekday::Wednesday, Weekday::Thursday, Weekday::Friday)->bySetPosition(-1)` |
| Every year (birthdays, holidays) | `RecurrenceRule::yearly()` |
| Every year until 2030 | `RecurrenceRule::yearly()->until('2030-12-31')` |

## Builder methods

| Method | Part | Notes |
|---|---|---|
| `new RecurrenceRule(Frequency)`, `daily()`, `weekly()`, `monthly()`, `yearly()` | `FREQ` | `Frequency` also has `Secondly`, `Minutely`, `Hourly` |
| `interval(int)` | `INTERVAL` | every n-th period |
| `count(int)` | `COUNT` | cannot be combined with `until()` |
| `until(date)` | `UNTIL` | inclusive; written as a DATE for all-day events |
| `byDay(Weekday\|string ...)` | `BYDAY` | `Weekday::Friday->nth(-1)` or strings like `'-1FR'` |
| `byMonthDay(int ...)` | `BYMONTHDAY` | 1–31 or -31–-1 |
| `byYearDay(int ...)` | `BYYEARDAY` | 1–366 or negative |
| `byWeekNumber(int ...)` | `BYWEEKNO` | 1–53 or negative |
| `byMonth(int ...)` | `BYMONTH` | 1–12 |
| `byHour(int ...)`, `byMinute(int ...)`, `bySecond(int ...)` | `BYHOUR`, `BYMINUTE`, `BYSECOND` | |
| `bySetPosition(int ...)` | `BYSETPOS` | picks the n-th occurrence of the set |
| `weekStart(Weekday)` | `WKST` | |

## Skipping occurrences

```php
$event->addExceptionDate('2026-10-29 09:30', '2026-12-31 09:30');
```

An exception date must match the start time of the occurrence it removes. For all-day events pass
the date only.
