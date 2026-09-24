<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * Status of a VEVENT (RFC 5545 §3.8.1.11).
 */
enum EventStatus: string
{
    case Tentative = 'TENTATIVE';
    case Confirmed = 'CONFIRMED';
    case Cancelled = 'CANCELLED';
}
