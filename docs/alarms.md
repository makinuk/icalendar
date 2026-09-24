# Alarms

`Makinuk\ICalendar\Component\Alarm` represents a `VALARM` (RFC 5545 §3.6.6), a reminder attached to
an event or a to-do with `addAlarm()`. An event can have several alarms.

## Types

```php
use Makinuk\ICalendar\Component\Alarm;

Alarm::display('Stand-up starts soon');                                   // a notification
Alarm::audio();                                                           // the client's default sound
Alarm::audio('https://example.com/sounds/bell.mp3');                      // a custom sound
Alarm::email('Tomorrow: kickoff', 'The agenda is attached.', 'team@example.com', 'boss@example.com');
```

> Most clients ignore `EMAIL` alarms in imported files for security reasons and many show `AUDIO`
> alarms as regular notifications. `DISPLAY` is the most portable choice.

## When it triggers

Without a trigger, an alarm fires **15 minutes before the start**.

```php
$alarm->before(minutes: 10);                  // 10 minutes before the start
$alarm->before(days: 1, hours: 2);            // 1 day and 2 hours before
$alarm->before();                             // at the start
$alarm->after(minutes: 5);                    // 5 minutes after the start
$alarm->before(hours: 1)->relativeToEnd();    // 1 hour before the end / due date
$alarm->at('2026-10-01 08:55');               // at an absolute time

use Makinuk\ICalendar\Value\Duration;
$alarm->setTrigger(Duration::minutes(30)->negate());
$alarm->setTrigger(new DateInterval('PT30M'));
```

## Repeating

```php
// Fire at the trigger, then 3 more times every 5 minutes
Alarm::display('Take your medicine')->before(minutes: 15)->repeat(3, Duration::minutes(5));
```

## Migrating from 2.x

`new ICalAlarm($day, $hour, $minute, $second)` becomes
`Alarm::display('Reminder')->before(days: $day, hours: $hour, minutes: $minute, seconds: $second)`.
