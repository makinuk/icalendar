<?php

namespace App\Fixture;

use makinuk\ICalendar\ICalAlarm;
use makinuk\ICalendar\ICalEvent;
use makinuk\ICalendar\ICalPerson;

function event(ICalPerson $organizer): string
{
    $full = new ICalEvent('uid-1', 1790841600, 1790845200, new ICalAlarm(0, 0, 15), 'Istanbul', $organizer, 'Description', 'Summary');
    $partial = new ICalEvent(null, 1790841600, null, null, 'Istanbul');
    $uidOnly = new ICalEvent('uid-2');
    $empty = new ICalEvent();

    return $full->getEventText() . $partial . $uidOnly . $empty;
}

?>
-----
<?php

namespace App\Fixture;

use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Property\Organizer;
use Makinuk\ICalendar\Component\Event;

function event(Organizer $organizer): string
{
    $full = (new Event('uid-1'))->setStart(1790841600)->setEnd(1790845200)->addAlarm(Alarm::display('Reminder')->before(minutes: 15))->setLocation('Istanbul')->setOrganizer($organizer)->setDescription('Description')->setSummary('Summary');
    $partial = (new Event())->setStart(1790841600)->setLocation('Istanbul');
    $uidOnly = new Event('uid-2');
    $empty = new Event();

    return $full->render() . $partial . $uidOnly . $empty;
}

?>
