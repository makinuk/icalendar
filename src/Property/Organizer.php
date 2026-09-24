<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Property;

/**
 * The ORGANIZER of an event or to-do (RFC 5545 §3.8.4.3).
 */
final class Organizer extends CalendarUser
{
    public function toProperty(): Property
    {
        return $this->createProperty('ORGANIZER');
    }
}
