<?php

// Signatures of the makinuk/icalendar 2.x API. Loaded by the upgrade-3.0 Rector set so that Rector
// can resolve the types of legacy code after 2.x has been replaced by 3.x in vendor/.
// Never loaded at runtime.

namespace makinuk\ICalendar;

class ICalendar
{
    public $Method;

    public function __construct(?ICalEvent $Event = null, $Method = 'PUBLISH') {}

    public function setMethod($Method): static
    {
        return $this;
    }

    public function addEvent(ICalEvent $Event): static
    {
        return $this;
    }

    public function show($fileName = 'iCalendar.ics'): void {}

    public function saveToFile($fileName): void {}

    public function getCalendarText(): string
    {
        return '';
    }

    public static function convertUTC($Timespan = null): int
    {
        return 0;
    }
}

class ICalEvent
{
    public function __construct($UId = null, $StartDate = null, $EndDate = null, ?ICalAlarm $Alarm = null, $Location = null, ?ICalPerson $Organizer = null, $Description = null, $Summary = null) {}

    public function setUId($UId): static
    {
        return $this;
    }

    public function setStartDate($StartDate): static
    {
        return $this;
    }

    public function setEndDate($EndDate): static
    {
        return $this;
    }

    public function setLocation($Location): static
    {
        return $this;
    }

    public function setOrganizer(ICalPerson $Organizer): static
    {
        return $this;
    }

    public function setDescription($Description): static
    {
        return $this;
    }

    public function setSummary($Summary): static
    {
        return $this;
    }

    public function setAlarm(ICalAlarm $Alarm): static
    {
        return $this;
    }

    public function addAttendee(ICalAttendee $Attendee): static
    {
        return $this;
    }

    public function getEventText(): string
    {
        return '';
    }
}

class ICalAlarm
{
    public $Day = 0;
    public $Hour = 0;
    public $Minute = 0;
    public $Second = 0;

    public function __construct($Day = 0, $Hour = 0, $Minute = 0, $Second = 0) {}

    public function getAlarmText(): string
    {
        return '';
    }
}

class ICalPerson
{
    public $Name = null;
    public $Email = null;

    public function __construct($Name = null, $Email = null) {}

    public function setName($Name): static
    {
        return $this;
    }

    public function setEmail($Email): static
    {
        return $this;
    }

    public function getPersonText(): string
    {
        return '';
    }
}

class ICalAttendee
{
    public ICalPerson $Person;
    public $RSVP;

    public function __construct(ICalPerson $Person, $Reply = false) {}

    public function getAttendeeText(): string
    {
        return '';
    }
}
