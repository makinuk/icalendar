<?php

require_once "../vendor/autoload.php";

$ical = new makinuk\ICalendar\ICalendar();
$event = new makinuk\ICalendar\ICalEvent();

$event->setUId("11223344")
        ->setStartDate(strtotime("+24 hours"))
        ->setEndDate(strtotime("+25 hours"))
        ->setSummary("Summary is here")
        ->setDescription("Description area is here")
        ->setLocation("Istanbul")
        ->setOrganizer(new makinuk\ICalendar\ICalPerson("Mustafa AKIN", "user@domain.com"))
        ->setAlarm(new makinuk\ICalendar\ICalAlarm(0, 1, 10, 0));

$ical->addEvent($event);

$ical->saveToFile(dirname(__FILE__).DIRECTORY_SEPARATOR."simpleEventAdd.ics");

?>
-----
<?php

use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Property\Organizer;
use Makinuk\ICalendar\Component\Alarm;

require_once "../vendor/autoload.php";

$ical = new Calendar();
$event = new Event();

$event->setUid("11223344")
        ->setStart(strtotime("+24 hours"))
        ->setEnd(strtotime("+25 hours"))
        ->setSummary("Summary is here")
        ->setDescription("Description area is here")
        ->setLocation("Istanbul")
        ->setOrganizer(new Organizer("user@domain.com", "Mustafa AKIN"))
        ->addAlarm(Alarm::display('Reminder')->before(hours: 1, minutes: 10));

$ical->add($event);

$ical->save(dirname(__FILE__).DIRECTORY_SEPARATOR."simpleEventAdd.ics");

?>
