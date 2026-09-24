<?php

namespace App\Fixture;

use makinuk\ICalendar\ICalAlarm;

function alarms(int $minutes): array
{
    return [
        new ICalAlarm(1),
        new ICalAlarm(0, 2, 30, 15),
        new ICalAlarm(0, 0, $minutes),
        (new ICalAlarm(0, 1))->getAlarmText(),
    ];
}

?>
-----
<?php

namespace App\Fixture;

use Makinuk\ICalendar\Component\Alarm;

function alarms(int $minutes): array
{
    return [
        Alarm::display('Reminder')->before(days: 1),
        Alarm::display('Reminder')->before(hours: 2, minutes: 30, seconds: 15),
        Alarm::display('Reminder')->before(minutes: $minutes),
        (Alarm::display('Reminder')->before(hours: 1))->render(),
    ];
}

?>
