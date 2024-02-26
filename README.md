# icalendar
iCalendar is a computer file format that allows Internet users to send meeting requests and tasks to other Internet users

This project follows [RFC 5545](http://tools.ietf.org/html/rfc5545)

# Composer
> composer require makinuk/icalendar>=1.0

# Example Usage
```php
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

//$ical->getCalendarText();
//$ical->show();
$ical->saveToFile(dirname(__FILE__).DIRECTORY_SEPARATOR."simpleEventAdd.ics");
```
