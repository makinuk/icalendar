<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * Participation status of an attendee (RFC 5545 §3.2.12).
 */
enum ParticipationStatus: string
{
    case NeedsAction = 'NEEDS-ACTION';
    case Accepted = 'ACCEPTED';
    case Declined = 'DECLINED';
    case Tentative = 'TENTATIVE';
    case Delegated = 'DELEGATED';
    case Completed = 'COMPLETED';
    case InProcess = 'IN-PROCESS';
}
