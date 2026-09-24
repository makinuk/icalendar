<?php

declare(strict_types=1);

use Makinuk\ICalendar\Rector\LegacyClass;
use Makinuk\ICalendar\Rector\Rule\ConvertUtcRector;
use Makinuk\ICalendar\Rector\Rule\NewAlarmRector;
use Makinuk\ICalendar\Rector\Rule\NewAttendeeRector;
use Makinuk\ICalendar\Rector\Rule\NewCalendarRector;
use Makinuk\ICalendar\Rector\Rule\NewEventRector;
use Makinuk\ICalendar\Rector\Rule\NewPersonRector;
use Makinuk\ICalendar\Rector\Rule\PersonPropertyToGetterRector;
use Makinuk\ICalendar\Rector\Rule\SetMethodToEnumRector;
use Makinuk\ICalendar\Rector\Rule\ShowToSendRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;

/*
 * Migrates code written for makinuk/icalendar 2.x to 3.0. See UPGRADE-3.0.md.
 */
return static function (RectorConfig $rectorConfig): void {
    // 2.x is no longer installed, so its signatures are loaded from a stub to resolve types.
    if (!class_exists(LegacyClass::EVENT)) {
        require_once __DIR__ . '/../stubs/icalendar-2.x.php';
    }

    $rectorConfig->rules([
        NewCalendarRector::class,
        SetMethodToEnumRector::class,
        ShowToSendRector::class,
        ConvertUtcRector::class,
        NewEventRector::class,
        NewAlarmRector::class,
        NewAttendeeRector::class,
        NewPersonRector::class,
        PersonPropertyToGetterRector::class,
    ]);

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename(LegacyClass::CALENDAR, 'addEvent', 'add'),
        new MethodCallRename(LegacyClass::CALENDAR, 'getCalendarText', 'render'),
        new MethodCallRename(LegacyClass::CALENDAR, 'saveToFile', 'save'),
        new MethodCallRename(LegacyClass::EVENT, 'setUId', 'setUid'),
        new MethodCallRename(LegacyClass::EVENT, 'setStartDate', 'setStart'),
        new MethodCallRename(LegacyClass::EVENT, 'setEndDate', 'setEnd'),
        new MethodCallRename(LegacyClass::EVENT, 'setAlarm', 'addAlarm'),
        new MethodCallRename(LegacyClass::EVENT, 'getEventText', 'render'),
        new MethodCallRename(LegacyClass::ALARM, 'getAlarmText', 'render'),
    ]);

    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        LegacyClass::CALENDAR => 'Makinuk\ICalendar\Calendar',
        LegacyClass::EVENT => 'Makinuk\ICalendar\Component\Event',
        LegacyClass::ALARM => 'Makinuk\ICalendar\Component\Alarm',
        LegacyClass::PERSON => 'Makinuk\ICalendar\Property\Organizer',
        LegacyClass::ATTENDEE => 'Makinuk\ICalendar\Property\Attendee',
    ]);
};
