<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Exception;

/**
 * Thrown when a value passed to the library is malformed or out of range.
 */
class InvalidArgumentException extends \InvalidArgumentException implements ICalendarException {}
