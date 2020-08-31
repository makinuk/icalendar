<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace makinuk\ICalendar;

/**
 * @author m.akin
 * Standart definations on http://tools.ietf.org/html/rfc5545
 */
class ICalendar {
	
	public $Method;
        protected $Events;
	
	/**
	 * @param ICalEvent $Event
	 * @return ICalendar
	 */
	public function __construct(ICalEvent $Event = null,$Method = "PUBLISH") {
		if ($Event != null) {
			$this->addEvent($Event);
		}
		
		$this->setMethod($Method);
		return $this;
	}
	
	
	/**
	 * @param string $Method PUBLISH or REQUEST
	 * @return ICalendar
	 */
	public function setMethod($Method) {$this->Method = $Method; return $this;}
	
	
	/**
	 * @param ICalEvent $Event
	 * @return ICalendar
	 */
	public function addEvent(ICalEvent $Event) {
		$this->Events[] =$Event;
		return $this;
	}
	
	public function show($fileName = "iCalendar.ics") {
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename='.$fileName);
		
		echo $this->getCalendarText();
		
	}
        
        public function saveToFile($fileName) {
            file_put_contents($fileName, $this->getCalendarText());
        }
	
	public function getCalendarText() {
		$Data = "BEGIN:VCALENDAR\n";
		$Data .= "PRODID:-//Microsoft Corporation//Outlook 12.0 MIMEDIR//EN\n";
		$Data .= "VERSION:2.0\n";
		$Data .= "METHOD:".$this->Method."\n";
		if (is_array($this->Events)) {
			foreach ($this->Events as $Event) {
				$Data .= $Event;
			}
		}
		return $Data .= "END:VCALENDAR";
	}
	
	/*
         * @TODO: this is not a good way fix it
         */
        public static function convertUTC($Timespan = null) {
                if (is_null($Timespan)) { $Timespan = time();}
		return $Utc = strtotime((date("Z",$Timespan)*-1)." seconds",$Timespan);
	}
}