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
        ->setOrganizer(new makinuk\ICalendar\ICalPerson("Mustafa AKIN", "m.akin@makinuk.com"))
        ->setAlarm(new makinuk\ICalendar\ICalAlarm(0, 1, 10, 0));

$ical->addEvent($event);

//$ical->getCalendarText();
//$ical->show();
$ical->saveToFile(dirname(__FILE__).DIRECTORY_SEPARATOR."simpleEventAdd.ics");