<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * Participation role of an attendee (RFC 5545 §3.2.16).
 */
enum Role: string
{
    case Chair = 'CHAIR';
    case RequiredParticipant = 'REQ-PARTICIPANT';
    case OptionalParticipant = 'OPT-PARTICIPANT';
    case NonParticipant = 'NON-PARTICIPANT';
}
