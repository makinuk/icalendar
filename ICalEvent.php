<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace makinuk\ICalendar;

/**
 * Description of ICalEvent
 *
 * @author m.akin
 */
class ICalEvent {

    private $UId = null;
    private $StartDate = null;
    private $EndDate = null;
    private $Location = null;
    private $Organizer = null;
    private $Description = null;
    private $Summary = null;
    private $Alarm = null;
    private $Attendee = Array();

    public function __construct($UId = null, $StartDate = null, $EndDate = null, ICalAlarm $Alarm = null, $Location = null, ICalPerson $Organizer = null, $Description = null, $Summary = null) {
        $this->setUId($UId)
                ->setStartDate($StartDate)
                ->setEndDate($EndDate)
                ->setLocation($Location)
                ->setDescription($Description)
                ->setSummary($Summary);

        if (!is_null($Alarm)) {
            $this->setAlarm($Alarm);
        }
        if (!is_null($Organizer)) {
            $this->setOrganizer($Organizer);
        }
    }

    public function setUId($UId) {
        $this->UId = $UId;
        return $this;
    }

    public function setStartDate($StartDate) {
        $this->StartDate = $StartDate;
        return $this;
    }

    public function setEndDate($EndDate) {
        $this->EndDate = $EndDate;
        return $this;
    }

    public function setLocation($Location) {
        $this->Location = preg_replace('/(\r|\n|\r\n){2,}/', '', $Location);
        return $this;
    }

    public function setOrganizer(ICalPerson $Organizer) {
        $this->Organizer = $Organizer;
        return $this;
    }

    public function setDescription($Description) {
        $this->Description = $Description;
        return $this;
    }

    public function setSummary($Summary) {
        $this->Summary = $Summary;
        return $this;
    }

    public function setAlarm(ICalAlarm $Alarm) {
        $this->Alarm = $Alarm;
        return $this;
    }

    public function addAttendee(ICalAttendee $Attendee) {
        $this->Attendee[] = $Attendee;
        return $this;
    }

    public function getEventText() {

        $VEVENT = "BEGIN:VEVENT\n";
        $VEVENT .= "CLASS:PUBLIC\n";
        $VEVENT .= "CREATED:" . date("Ymd\THis\Z", ICalendar::ConvertUTC()) . "\n";
        $VEVENT .= "DTSTAMP:" . date("Ymd\THis\Z", ICalendar::ConvertUTC($this->StartDate)) . "\n";
        $VEVENT .= "DTSTART:" . date("Ymd\THis\Z", ICalendar::ConvertUTC($this->StartDate)) . "\n";
        $VEVENT .= "DTEND:" . date("Ymd\THis\Z", ICalendar::ConvertUTC($this->EndDate)) . "\n";
        $VEVENT .= "LOCATION:" . $this->Location . "\n";
        $VEVENT .= "ORGANIZER;CN=" . $this->Organizer . "\n";
        foreach ($this->Attendee as $Attendee) {
            $VEVENT .= $Attendee;
        }
        $VEVENT .= "DESCRIPTION;LANGUAGE=tr:" . $this->Description . "\n";
        $VEVENT .= "PRIORITY:5\n";
        $VEVENT .= "SEQUENCE:0\n";
        $VEVENT .= "SUMMARY;LANGUAGE=tr:" . $this->Summary . "\n";
        $VEVENT .= "TRANSP:OPAQUE\n";
        $VEVENT .= "UID:" . $this->UId . "\n";
        $VEVENT .= $this->Alarm;
        
        return $VEVENT .= "END:VEVENT\n";
    }

    public function __toString() {
        return (string) $this->getEventText();
    }

}
