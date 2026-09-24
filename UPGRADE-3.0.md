# Upgrading from 2.x to 3.0

3.0 is a rewrite that makes the output RFC 5545 compliant and modernises the API. Most projects
need to change only a few lines, and most of them can be changed automatically with
[Rector](#automated-upgrade-with-rector). The 2.x line stays available (`"makinuk/icalendar": "^2.1"`) but
is no longer maintained.

## Automated upgrade with Rector

The package ships a [Rector](https://getrector.com) set that rewrites 2.x code to the 3.0 API:
class names, imports, method names, constructors, alarms, attendees and method strings.

1. Upgrade the package and install Rector:

   ```bash
   composer require makinuk/icalendar:^3.1
   composer require --dev rector/rector
   ```

2. Create `rector.php` in your project root (or add the set to your existing configuration):

   ```php
   <?php

   use Makinuk\ICalendar\Rector\Set\ICalendarSetList;
   use Rector\Config\RectorConfig;

   return RectorConfig::configure()
       ->withPaths([__DIR__ . '/src'])
       ->withSets([ICalendarSetList::UPGRADE_30])
       ->withImportNames(removeUnusedImports: true);
   ```

3. Preview the changes, then apply them:

   ```bash
   vendor/bin/rector process --dry-run
   vendor/bin/rector process
   ```

4. Run your coding style fixer and your tests, and review the [output changes](#output-changes).

For example, the 2.x README example becomes:

```diff
-$ical = new makinuk\ICalendar\ICalendar();
-$event = new makinuk\ICalendar\ICalEvent();
-$event->setUId("11223344")
-        ->setStartDate(strtotime("+24 hours"))
-        ->setOrganizer(new makinuk\ICalendar\ICalPerson("Mustafa AKIN", "user@domain.com"))
-        ->setAlarm(new makinuk\ICalendar\ICalAlarm(0, 1, 10, 0));
-$ical->addEvent($event);
-$ical->saveToFile("simpleEventAdd.ics");
+$ical = new Calendar();
+$event = new Event();
+$event->setUid("11223344")
+        ->setStart(strtotime("+24 hours"))
+        ->setOrganizer(new Organizer("user@domain.com", "Mustafa AKIN"))
+        ->addAlarm(Alarm::display('Reminder')->before(hours: 1, minutes: 10));
+$ical->add($event);
+$ical->save("simpleEventAdd.ics");
```

Rector only changes calls whose type it can prove, so your own classes with methods such as `show()`
or `addEvent()` are left alone. Review these cases by hand:

| Case | What to do |
|---|---|
| Variables Rector cannot type, e.g. an untyped function return value | add a type declaration and run Rector again, or migrate by hand |
| `new ICalPerson()` with fewer than two arguments, `setName()` / `setEmail()` | pass the e-mail and name to the `Organizer` / `Attendee` constructor |
| Writes to `$person->Name`, `$person->Email`, reads of `$attendee->Person`, `$attendee->RSVP`, `$calendar->Method` | use the constructors and getters of 3.0 |
| `getPersonText()`, `getAttendeeText()` | use `toProperty()->toString()` |
| `new ICalendar()` without a method | 2.x always wrote `METHOD:PUBLISH`, 3.0 writes no method; add `->setMethod(Method::Publish)` if you rely on it |
| `new ICalAlarm()` with only zeros | 2.x wrote no alarm at all, the migrated `->before()` fires at the start; remove the alarm if that was the intent |
| Method strings that are not iTIP methods | `Method::from('X-CUSTOM')` throws a `ValueError`; choose a `Method` case |

The set requires Rector 2.

## Requirements

- PHP **8.2** or newer (was 7.4)

## Namespace

The vendor namespace is now StudlyCaps, and classes moved into sub-namespaces:

| 2.x | 3.0 |
|---|---|
| `makinuk\ICalendar\ICalendar` | `Makinuk\ICalendar\Calendar` |
| `makinuk\ICalendar\ICalEvent` | `Makinuk\ICalendar\Component\Event` |
| `makinuk\ICalendar\ICalAlarm` | `Makinuk\ICalendar\Component\Alarm` |
| `makinuk\ICalendar\ICalPerson` | `Makinuk\ICalendar\Property\Organizer` (or `Attendee`) |
| `makinuk\ICalendar\ICalAttendee` | `Makinuk\ICalendar\Property\Attendee` |

## Methods

| 2.x | 3.0 |
|---|---|
| `new ICalendar($event, 'REQUEST')` | `(new Calendar($event))->setMethod(Method::Request)` |
| `$ical->setMethod('PUBLISH')` | `$calendar->setMethod(Method::Publish)` |
| `$ical->addEvent($event)` | `$calendar->add($event)` |
| `$ical->getCalendarText()` | `$calendar->render()` |
| `$ical->saveToFile($path)` | `$calendar->save($path)` (throws `IOException` on failure) |
| `$ical->show($fileName)` | `$calendar->send($fileName, asAttachment: false)` |
| `ICalendar::convertUTC()` | removed; pass any date and it is converted to UTC for you |
| `new ICalEvent($uid, $start, $end, ...)` | `new Event($uid)` + setters, or `Event::create($summary, $start, $end)` |
| `$event->setUId($uid)` | `$event->setUid($uid)` |
| `$event->setStartDate($timestamp)` | `$event->setStart($timestamp)` |
| `$event->setEndDate($timestamp)` | `$event->setEnd($timestamp)` |
| `$event->setAlarm($alarm)` | `$event->addAlarm($alarm)` |
| `$event->setOrganizer(new ICalPerson($name, $email))` | `$event->setOrganizer($email, $name)` (**argument order changed**) |
| `$event->addAttendee(new ICalAttendee(new ICalPerson($name, $email), true))` | `$event->addAttendee(new Attendee($email, $name, rsvp: true))` |
| `new ICalAlarm($day, $hour, $minute, $second)` | `Alarm::display('Reminder')->before(days: $day, hours: $hour, minutes: $minute, seconds: $second)` |
| `$event->getEventText()` / `__toString()` | `$event->render()` / `__toString()` |

Unix timestamps are still accepted everywhere, as are `DateTimeInterface` objects and date strings.
Public properties such as `$person->Name` are gone; use the getters (`getName()`, `getEmail()`...).

## Before and after

```php
// 2.x
$ical = new makinuk\ICalendar\ICalendar();
$event = new makinuk\ICalendar\ICalEvent();
$event->setUId('11223344')
    ->setStartDate(strtotime('+24 hours'))
    ->setEndDate(strtotime('+25 hours'))
    ->setSummary('Summary is here')
    ->setOrganizer(new makinuk\ICalendar\ICalPerson('Mustafa AKIN', 'user@domain.com'))
    ->setAlarm(new makinuk\ICalendar\ICalAlarm(0, 1, 10, 0));
$ical->addEvent($event);
$ical->saveToFile('event.ics');
```

```php
// 3.0
use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Component\Event;

$event = (new Event('11223344'))
    ->setStart(strtotime('+24 hours'))
    ->setEnd(strtotime('+25 hours'))
    ->setSummary('Summary is here')
    ->setOrganizer('user@domain.com', 'Mustafa AKIN')
    ->addAlarm(Alarm::display('Summary is here')->before(hours: 1, minutes: 10));

(new Calendar($event))->save('event.ics');
```

## Output changes

The generated file changes even when your code does not. All changes follow the standard:

- lines end with CRLF and long lines are folded
- `,` `;` `\` and line breaks in text are escaped; multi-line descriptions no longer break the file
- `DTSTAMP` is the time the object was created (was the start date)
- `PRODID` is `-//makinuk//iCalendar 3.0//EN` (was Outlook's); change it with `setProductId()`
- `METHOD` is written only when you set it (was always `PUBLISH`); call `setMethod(Method::Publish)` to keep it
- `CALSCALE:GREGORIAN` is added
- the hard-coded `CLASS:PUBLIC`, `PRIORITY:5`, `SEQUENCE:0`, `TRANSP:OPAQUE` and `LANGUAGE=tr` are no longer written; set them explicitly when you need them
- empty `LOCATION` and `ORGANIZER` lines are no longer written
- `CN` values are quoted only when needed
- a `UID` is generated when you do not set one
- `render()` throws a `ValidationException` for invalid data (for example an event without start) instead of producing a broken file
