<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace makinuk\Icalendar;

/**
 * Description of ICalAttendee
 *
 * @author m.akin
 */
class ICalAttendee {
    /**
     *
     * @var ICalPerson 
     */
    public $Person;
    public $RSVP;

    public function __construct(ICalPerson $Person, $Reply = false) {
        $this->Person = $Person;
        $this->RSVP = $Reply;
    }

    public function getAttendeeText() {
        
        return 'ATTENDEE;CN="' . $this->Person->Name . '";RSVP=' . ($this->RSVP ? "TRUE" : "FALSE") . ':mailto:' . $this->Person->Email . "\n";
    }

    public function __toString() {
        return $this->getAttendeeText();
    }
}
