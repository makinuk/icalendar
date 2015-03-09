<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace makinuk\ICalendar;

/**
 * Description of ICalPerson
 *
 * @author m.akin
 */
class ICalPerson {

    public $Name = null;
    public $Email = null;

    public function __construct($Name = null, $Email = null) {
        $this->setName($Name)
                ->setEmail($Email);
    }

    public function setName($Name) {
        $this->Name = $Name;
        return $this;
    }

    public function setEmail($Email) {
        $this->Email = $Email;
        return $this;
    }

    public function getPersonText() {
        return '"' . $this->Name . '":mailto:' . $this->Email;
    }

    public function __toString() {
        return $this->getPersonText();
    }

}
