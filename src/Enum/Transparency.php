<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * Whether an event blocks time on a free/busy lookup (RFC 5545 §3.8.2.7).
 */
enum Transparency: string
{
    case Opaque = 'OPAQUE';
    case Transparent = 'TRANSPARENT';
}
