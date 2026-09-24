<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Exception;

/**
 * Thrown on render when a component is incomplete or inconsistent with RFC 5545.
 */
class ValidationException extends \DomainException implements ICalendarException {}
