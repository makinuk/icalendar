<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Component;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Makinuk\ICalendar\Enum\EventStatus;
use Makinuk\ICalendar\Enum\Transparency;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Property\Property;
use Makinuk\ICalendar\Support\DateTimeNormalizer;
use Makinuk\ICalendar\Value\Duration;

/**
 * A VEVENT: a meeting, an appointment or an all-day event (RFC 5545 §3.6.1).
 *
 *     Event::create('Sprint review', '2026-10-01 14:00', '2026-10-01 15:00')
 *         ->setLocation('Room 4')
 *         ->addAlarm(Alarm::display('Sprint review')->before(minutes: 15));
 */
final class Event extends CalendarComponent
{
    private ?DateTimeImmutable $end = null;
    private ?Duration $duration = null;
    private ?EventStatus $status = null;
    private ?Transparency $transparency = null;

    public static function create(
        string $summary,
        DateTimeInterface|string|int $start,
        DateTimeInterface|string|int|null $end = null,
    ): self {
        return (new self())->setSummary($summary)->setStart($start)->setEnd($end);
    }

    public function getComponentName(): string
    {
        return 'VEVENT';
    }

    /**
     * Sets DTEND and clears any duration. For all-day events the end date is exclusive:
     * a single-day event on 1 October ends on 2 October, or has no end at all.
     */
    public function setEnd(DateTimeInterface|string|int|null $end): self
    {
        $this->end = $end === null ? null : DateTimeNormalizer::normalize($end);
        if ($end !== null) {
            $this->duration = null;
        }

        return $this;
    }

    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * Sets DURATION and clears any end date.
     */
    public function setDuration(Duration|DateInterval|null $duration): self
    {
        $this->duration = $duration instanceof DateInterval ? Duration::fromDateInterval($duration) : $duration;
        if ($duration !== null) {
            $this->end = null;
        }

        return $this;
    }

    public function getDuration(): ?Duration
    {
        return $this->duration;
    }

    public function setStatus(?EventStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?EventStatus
    {
        return $this->status;
    }

    /**
     * Transparent events do not block time on free/busy lookups.
     */
    public function setTransparency(?Transparency $transparency): self
    {
        $this->transparency = $transparency;

        return $this;
    }

    protected function validate(): void
    {
        parent::validate();

        $start = $this->getStart();
        if ($start === null) {
            throw new ValidationException(sprintf('Event "%s" needs a start date (DTSTART).', $this->getUid()));
        }

        if ($this->end !== null) {
            $invalid = $this->isAllDay()
                ? $this->end->format('Y-m-d') <= $start->format('Y-m-d')
                : $this->end < $start;
            if ($invalid) {
                throw new ValidationException(sprintf(
                    'Event "%s" ends before it starts%s.',
                    $this->getUid(),
                    $this->isAllDay() ? ' (the end date of an all-day event is exclusive)' : '',
                ));
            }
        }

        if ($this->duration !== null && $this->duration->isNegative()) {
            throw new ValidationException(sprintf('Event "%s" has a negative duration.', $this->getUid()));
        }
    }

    protected function buildTimeProperties(): iterable
    {
        $start = $this->getStart();
        if ($start !== null) {
            yield $this->dateProperty('DTSTART', $start);
        }
        if ($this->end !== null) {
            yield $this->dateProperty('DTEND', $this->end);
        } elseif ($this->duration !== null) {
            yield new Property('DURATION', $this->duration->toString());
        }
    }

    protected function buildStatusProperties(): iterable
    {
        if ($this->status !== null) {
            yield new Property('STATUS', $this->status->value);
        }
        if ($this->transparency !== null) {
            yield new Property('TRANSP', $this->transparency->value);
        }
    }
}
