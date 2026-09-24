<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * Recurrence frequency (RFC 5545 §3.3.10).
 */
enum Frequency: string
{
    case Secondly = 'SECONDLY';
    case Minutely = 'MINUTELY';
    case Hourly = 'HOURLY';
    case Daily = 'DAILY';
    case Weekly = 'WEEKLY';
    case Monthly = 'MONTHLY';
    case Yearly = 'YEARLY';
}
