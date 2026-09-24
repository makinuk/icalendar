<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Value;

use DateInterval;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Stringable;

/**
 * An immutable DURATION value, e.g. PT1H30M or -P1D (RFC 5545 §3.3.6).
 *
 *     Duration::of(hours: 1, minutes: 30);
 *     Duration::minutes(15)->negate();   // 15 minutes before
 */
final class Duration implements Stringable
{
    private function __construct(
        private readonly int $weeks,
        private readonly int $days,
        private readonly int $hours,
        private readonly int $minutes,
        private readonly int $seconds,
        private readonly bool $negative = false,
    ) {
        foreach ([$weeks, $days, $hours, $minutes, $seconds] as $part) {
            if ($part < 0) {
                throw new InvalidArgumentException('Duration parts must not be negative; use negate() instead.');
            }
        }
    }

    public static function of(int $weeks = 0, int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): self
    {
        return new self($weeks, $days, $hours, $minutes, $seconds);
    }

    public static function weeks(int $weeks): self
    {
        return self::of(weeks: $weeks);
    }

    public static function days(int $days): self
    {
        return self::of(days: $days);
    }

    public static function hours(int $hours): self
    {
        return self::of(hours: $hours);
    }

    public static function minutes(int $minutes): self
    {
        return self::of(minutes: $minutes);
    }

    public static function seconds(int $seconds): self
    {
        return self::of(seconds: $seconds);
    }

    /**
     * Converts a DateInterval. Months and years are rejected because their length is not fixed.
     */
    public static function fromDateInterval(DateInterval $interval): self
    {
        if ($interval->y !== 0 || $interval->m !== 0) {
            throw new InvalidArgumentException('A duration cannot contain months or years; express it in days instead.');
        }

        return new self(0, (int) $interval->d, $interval->h, $interval->i, $interval->s, $interval->invert === 1);
    }

    /**
     * Returns the same duration pointing in the opposite direction.
     */
    public function negate(): self
    {
        return new self($this->weeks, $this->days, $this->hours, $this->minutes, $this->seconds, !$this->negative);
    }

    public function isNegative(): bool
    {
        return $this->negative;
    }

    public function isZero(): bool
    {
        return $this->weeks + $this->days + $this->hours + $this->minutes + $this->seconds === 0;
    }

    public function toString(): string
    {
        if ($this->isZero()) {
            return 'PT0S';
        }

        $sign = $this->negative ? '-' : '';
        $timeless = $this->hours + $this->minutes + $this->seconds === 0;

        // dur-week cannot be combined with other parts, so mixed durations are expressed in days.
        if ($this->days === 0 && $timeless) {
            return $sign . 'P' . $this->weeks . 'W';
        }

        $result = $sign . 'P';
        $days = $this->weeks * 7 + $this->days;
        if ($days > 0) {
            $result .= $days . 'D';
        }
        if ($timeless) {
            return $result;
        }

        // dur-time only allows H[M[S]], M[S] or S, so a skipped minute part is written as 0M.
        $result .= 'T';
        if ($this->hours > 0) {
            $result .= $this->hours . 'H';
        }
        if ($this->minutes > 0 || ($this->hours > 0 && $this->seconds > 0)) {
            $result .= $this->minutes . 'M';
        }
        if ($this->seconds > 0) {
            $result .= $this->seconds . 'S';
        }

        return $result;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
