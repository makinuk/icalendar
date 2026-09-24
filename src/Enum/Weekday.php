<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

use Makinuk\ICalendar\Exception\InvalidArgumentException;

/**
 * Day of the week as used by BYDAY and WKST (RFC 5545 §3.3.10).
 */
enum Weekday: string
{
    case Monday = 'MO';
    case Tuesday = 'TU';
    case Wednesday = 'WE';
    case Thursday = 'TH';
    case Friday = 'FR';
    case Saturday = 'SA';
    case Sunday = 'SU';

    /**
     * Returns the n-th occurrence of this weekday within the period, e.g. Weekday::Friday->nth(-1)
     * for "last Friday" or Weekday::Monday->nth(2) for "second Monday".
     */
    public function nth(int $occurrence): string
    {
        if ($occurrence === 0 || abs($occurrence) > 53) {
            throw new InvalidArgumentException('Weekday occurrence must be between -53 and 53, excluding 0.');
        }

        return $occurrence . $this->value;
    }
}
