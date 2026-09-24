<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * Status of a VTODO (RFC 5545 §3.8.1.11).
 */
enum TodoStatus: string
{
    case NeedsAction = 'NEEDS-ACTION';
    case Completed = 'COMPLETED';
    case InProcess = 'IN-PROCESS';
    case Cancelled = 'CANCELLED';
}
