<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Support;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Makinuk\ICalendar\Exception\InvalidArgumentException;

/**
 * Converts the date inputs accepted by the public API into DateTimeImmutable.
 *
 * @internal
 */
final class DateTimeNormalizer
{
    /**
     * @param DateTimeInterface|string|int $value a date object, any string understood by
     *                                            DateTimeImmutable, or a Unix timestamp
     */
    public static function normalize(DateTimeInterface|string|int $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        $timezone = new DateTimeZone(date_default_timezone_get());

        if (is_int($value)) {
            return (new DateTimeImmutable('@' . $value))->setTimezone($timezone);
        }

        try {
            return new DateTimeImmutable($value, $timezone);
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('Invalid date/time value "%s".', $value), 0, $e);
        }
    }
}
