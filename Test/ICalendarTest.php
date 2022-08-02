<?php


use makinuk\ICalendar\ICalendar;
use makinuk\ICalendar\ICalEvent;
use makinuk\ICalendar\ICalPerson;
use PHPUnit\Framework\TestCase;

class ICalendarTest extends TestCase
{
    /** @var ICalendar */
    protected $ical;

    protected function setUp()
    {
        $this->ical = new ICalendar();
    }


    public function testSaveToFile()
    {
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . "icalOut.ics";

        if (file_exists($file)) {
            unlink($file);
        }

        $event = new ICalEvent();
        $event->setUId(1234)
            ->setSummary("Summary")
            ->setOrganizer(new ICalPerson("Mustafa AKIN", "m.akin@makinuk.com"))
            ->setLocation("Istanbul")
            ->setEndDate(strtotime("+26 hours"))
            ->setStartDate(strtotime("+24 hours"))
            ->setDescription("Description");
        $this->ical->addEvent($event);
        $this->ical->saveToFile($file);


        $this->assertFileExists($file,"calendar file not exists");
    }
}
