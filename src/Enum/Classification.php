<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * Access classification of a component (RFC 5545 §3.8.1.3).
 */
enum Classification: string
{
    case Public = 'PUBLIC';
    case Private = 'PRIVATE';
    case Confidential = 'CONFIDENTIAL';
}
