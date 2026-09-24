<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * Action performed when an alarm triggers (RFC 5545 §3.8.6.1).
 */
enum AlarmAction: string
{
    case Audio = 'AUDIO';
    case Display = 'DISPLAY';
    case Email = 'EMAIL';
}
