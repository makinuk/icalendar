# Extending

## Properties without a dedicated setter

Every component (including `Calendar`) accepts raw properties through `addProperty()`. This covers
vendor extensions and RFC properties that do not have a setter yet.

```php
use Makinuk\ICalendar\Property\Property;

// TEXT values: escaped for you
$event->addProperty(Property::text('X-MICROSOFT-CDO-BUSYSTATUS', 'OOF'));
$event->addProperty(Property::text('COMMENT', 'Bring your laptop, charger and badge'));

// Other value types: passed through as they are
$event->addProperty(new Property('X-MICROSOFT-CDO-ALLDAYEVENT', 'TRUE'));
$event->addProperty(new Property('CONFERENCE', 'https://meet.example.com/abc', [
    'VALUE' => 'URI',
    'FEATURE' => ['AUDIO', 'VIDEO'],
    'LABEL' => 'Join the call',
]));

$calendar->addProperty(Property::text('X-WR-TIMEZONE', 'Europe/Istanbul'));
```

Parameter values are quoted and encoded (RFC 6868) automatically. Raw values may not contain line
breaks; use `Property::text()` for text.

If you find yourself adding the same property in many projects, consider
[contributing](../CONTRIBUTING.md) a typed setter.

## Architecture

```text
src/
├── Calendar.php                 VCALENDAR: container, render / save / send
├── Component/
│   ├── Component.php            abstract base: BEGIN/END, properties, children, validation
│   ├── CalendarComponent.php    abstract base of VEVENT and VTODO: shared properties
│   ├── Event.php                VEVENT
│   ├── Todo.php                 VTODO
│   └── Alarm.php                VALARM
├── Property/
│   ├── Property.php             one content line: NAME;PARAM=value:VALUE
│   ├── CalendarUser.php         base of Organizer and Attendee (CAL-ADDRESS)
│   ├── Organizer.php
│   └── Attendee.php
├── Value/
│   ├── Duration.php             DURATION values
│   └── RecurrenceRule.php       RRULE values
├── Enum/                        allowed values (Method, EventStatus, Role...)
├── Exception/                   ICalendarException and its implementations
└── Support/                     internal helpers: formatting, dates, UIDs
```

Rendering is a single pass:

1. `Calendar::render()` calls `Component::render()`
2. `render()` calls `validate()`, then writes `BEGIN:<name>`, the properties from `buildProperties()`,
   the properties added with `addProperty()`, the children from `buildChildren()` and `END:<name>`
3. each `Property` renders itself, escaped and folded, by way of `Support\Formatter`

Classes in `Support\` are internal (`@internal`) and may change in minor versions. Everything
else follows [semantic versioning](https://semver.org).

## Adding a component

A new schedulable component, such as `VJOURNAL`, extends `CalendarComponent` and gets UID, DTSTAMP,
summary, description, attendees, recurrence and the rest for free:

```php
final class Journal extends CalendarComponent
{
    public function getComponentName(): string
    {
        return 'VJOURNAL';
    }

    protected function buildTimeProperties(): iterable
    {
        if ($this->getStart() !== null) {
            yield $this->dateProperty('DTSTART', $this->getStart());
        }
    }
}

$calendar->add((new Journal())->setStart('2026-10-01')->setAllDay()->setSummary('Retro notes'));
```

Components that are not schedulable (`VTIMEZONE`, `VFREEBUSY`) extend `Component` directly and
implement `getComponentName()`, `buildProperties()` and, when needed, `validate()` and `buildChildren()`.

Contributions of new components are welcome; see the [roadmap](rfc-compliance.md#roadmap).
