<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Exception;

/**
 * Thrown when a calendar cannot be written to its destination.
 */
class IOException extends \RuntimeException implements ICalendarException {}
