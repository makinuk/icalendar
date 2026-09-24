<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Value;

use DateTimeImmutable;
use DateTimeInterface;
use Makinuk\ICalendar\Enum\Frequency;
use Makinuk\ICalendar\Enum\Weekday;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Support\DateTimeNormalizer;
use Makinuk\ICalendar\Support\Formatter;
use Stringable;

/**
 * Fluent builder for an RRULE value (RFC 5545 §3.3.10).
 *
 *     RecurrenceRule::weekly()->byDay(Weekday::Monday, Weekday::Wednesday)->count(10);
 *     RecurrenceRule::monthly()->byDay(Weekday::Friday->nth(-1));   // last Friday of every month
 */
final class RecurrenceRule implements Stringable
{
    private const BY_DAY_PATTERN = '/^([+-]?([1-9]|[1-4][0-9]|5[0-3]))?(MO|TU|WE|TH|FR|SA|SU)$/';

    private ?int $interval = null;
    private ?int $count = null;
    private ?DateTimeImmutable $until = null;
    private ?Weekday $weekStart = null;

    /** @var array<string, list<string>> */
    private array $by = [];

    public function __construct(private readonly Frequency $frequency) {}

    public static function daily(): self
    {
        return new self(Frequency::Daily);
    }

    public static function weekly(): self
    {
        return new self(Frequency::Weekly);
    }

    public static function monthly(): self
    {
        return new self(Frequency::Monthly);
    }

    public static function yearly(): self
    {
        return new self(Frequency::Yearly);
    }

    public function getFrequency(): Frequency
    {
        return $this->frequency;
    }

    /**
     * Repeats every n-th period, e.g. interval(2) on a weekly rule means every other week.
     */
    public function interval(int $interval): self
    {
        if ($interval < 1) {
            throw new InvalidArgumentException('INTERVAL must be a positive integer.');
        }
        $this->interval = $interval;

        return $this;
    }

    /**
     * Stops after the given number of occurrences. Cannot be combined with until().
     */
    public function count(int $count): self
    {
        if ($count < 1) {
            throw new InvalidArgumentException('COUNT must be a positive integer.');
        }
        if ($this->until !== null) {
            throw new InvalidArgumentException('COUNT and UNTIL cannot be used together.');
        }
        $this->count = $count;

        return $this;
    }

    /**
     * Stops at the given date (inclusive). Cannot be combined with count().
     */
    public function until(DateTimeInterface|string|int $until): self
    {
        if ($this->count !== null) {
            throw new InvalidArgumentException('COUNT and UNTIL cannot be used together.');
        }
        $this->until = DateTimeNormalizer::normalize($until);

        return $this;
    }

    /**
     * @param Weekday|string ...$days weekdays, optionally with an ordinal such as "-1FR" (see Weekday::nth())
     */
    public function byDay(Weekday|string ...$days): self
    {
        $values = [];
        foreach ($days as $day) {
            $day = $day instanceof Weekday ? $day->value : strtoupper($day);
            if (preg_match(self::BY_DAY_PATTERN, $day) !== 1) {
                throw new InvalidArgumentException(sprintf('Invalid BYDAY value "%s".', $day));
            }
            $values[] = $day;
        }

        return $this->setBy('BYDAY', $values);
    }

    public function byMonthDay(int ...$days): self
    {
        return $this->setBy('BYMONTHDAY', self::integers($days, 31, true));
    }

    public function byYearDay(int ...$days): self
    {
        return $this->setBy('BYYEARDAY', self::integers($days, 366, true));
    }

    public function byWeekNumber(int ...$weeks): self
    {
        return $this->setBy('BYWEEKNO', self::integers($weeks, 53, true));
    }

    public function byMonth(int ...$months): self
    {
        return $this->setBy('BYMONTH', self::integers($months, 12, false, 1));
    }

    public function byHour(int ...$hours): self
    {
        return $this->setBy('BYHOUR', self::integers($hours, 23, false));
    }

    public function byMinute(int ...$minutes): self
    {
        return $this->setBy('BYMINUTE', self::integers($minutes, 59, false));
    }

    public function bySecond(int ...$seconds): self
    {
        return $this->setBy('BYSECOND', self::integers($seconds, 60, false));
    }

    /**
     * Selects the n-th occurrences within the set produced by the other BY* rules.
     */
    public function bySetPosition(int ...$positions): self
    {
        return $this->setBy('BYSETPOS', self::integers($positions, 366, true));
    }

    public function weekStart(Weekday $weekday): self
    {
        $this->weekStart = $weekday;

        return $this;
    }

    /**
     * @param bool $dateOnly render UNTIL as a DATE, required when the component starts on a DATE
     */
    public function toString(bool $dateOnly = false): string
    {
        $parts = ['FREQ=' . $this->frequency->value];

        if ($this->until !== null) {
            $parts[] = 'UNTIL=' . ($dateOnly ? Formatter::formatDate($this->until) : Formatter::formatDateTime($this->until));
        }
        if ($this->count !== null) {
            $parts[] = 'COUNT=' . $this->count;
        }
        if ($this->interval !== null) {
            $parts[] = 'INTERVAL=' . $this->interval;
        }
        foreach ($this->by as $name => $values) {
            $parts[] = $name . '=' . implode(',', $values);
        }
        if ($this->weekStart !== null) {
            $parts[] = 'WKST=' . $this->weekStart->value;
        }

        return implode(';', $parts);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param list<string> $values
     */
    private function setBy(string $name, array $values): self
    {
        if ($values === []) {
            throw new InvalidArgumentException(sprintf('%s requires at least one value.', $name));
        }
        $this->by[$name] = $values;

        return $this;
    }

    /**
     * @param array<int> $values
     *
     * @return list<string>
     */
    private static function integers(array $values, int $max, bool $allowNegative, int $min = 0): array
    {
        $result = [];
        foreach ($values as $value) {
            $valid = $allowNegative
                ? $value !== 0 && abs($value) <= $max
                : $value >= $min && $value <= $max;
            if (!$valid) {
                throw new InvalidArgumentException(sprintf('Recurrence value %d is out of range.', $value));
            }
            $result[] = (string) $value;
        }

        return $result;
    }
}
