<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace makinuk\ICalendar;

/**
 * Description of ICalAlarm
 *
 * @author m.akin
 */
Class ICalAlarm {
	
	public $Day = 0;
	public $Hour = 0;
	public $Minute = 0;
	public $Second = 0;
	
	public function __construct($Day = 0 ,$Hour = 0,$Minute = 0,$Second = 0) {
		$this->Day = (integer) $Day;
		$this->Hour = (integer) $Hour;
		$this->Minute = (integer) $Minute;
		$this->Second = (integer) $Second;
	}
	
	public function getAlarmText() {
		
		$VALARM = "";
		
		if ($this->Day != 0 || $this->Hour != 0 || $this->Minute != 0 || $this->Second != 0) {
			$VALARM = "BEGIN:VALARM\n";
			$VALARM .= "TRIGGER:-P".$this->Day."DT".$this->Hour."H".$this->Minute."M".$this->Second."S\n";
			$VALARM .= "ACTION:DISPLAY\n";
			$VALARM .= "DESCRIPTION:Reminder\n";
			$VALARM .= "END:VALARM\n";
		}
		return $VALARM;
	}
	
	public function __toString() {
		return $this->getAlarmText();
	}
}
