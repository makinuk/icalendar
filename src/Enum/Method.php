<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Enum;

/**
 * iTIP method of a calendar object (RFC 5546 §1.4).
 */
enum Method: string
{
    case Publish = 'PUBLISH';
    case Request = 'REQUEST';
    case Reply = 'REPLY';
    case Add = 'ADD';
    case Cancel = 'CANCEL';
    case Refresh = 'REFRESH';
    case Counter = 'COUNTER';
    case DeclineCounter = 'DECLINECOUNTER';
}
