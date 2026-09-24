<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Component;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Makinuk\ICalendar\Enum\TodoStatus;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Property\Property;
use Makinuk\ICalendar\Support\DateTimeNormalizer;
use Makinuk\ICalendar\Support\Formatter;
use Makinuk\ICalendar\Value\Duration;

/**
 * A VTODO: a task, optionally with a due date (RFC 5545 §3.6.2).
 *
 *     Todo::create('Send the quarterly report', '2026-10-15 17:00')->setPriority(1);
 */
final class Todo extends CalendarComponent
{
    private ?DateTimeImmutable $due = null;
    private ?Duration $duration = null;
    private ?DateTimeImmutable $completed = null;
    private ?int $percentComplete = null;
    private ?TodoStatus $status = null;

    public static function create(string $summary, DateTimeInterface|string|int|null $due = null): self
    {
        return (new self())->setSummary($summary)->setDue($due);
    }

    public function getComponentName(): string
    {
        return 'VTODO';
    }

    /**
     * Sets DUE and clears any duration.
     */
    public function setDue(DateTimeInterface|string|int|null $due): self
    {
        $this->due = $due === null ? null : DateTimeNormalizer::normalize($due);
        if ($due !== null) {
            $this->duration = null;
        }

        return $this;
    }

    public function getDue(): ?DateTimeImmutable
    {
        return $this->due;
    }

    /**
     * Sets DURATION and clears any due date. Requires a start date.
     */
    public function setDuration(Duration|DateInterval|null $duration): self
    {
        $this->duration = $duration instanceof DateInterval ? Duration::fromDateInterval($duration) : $duration;
        if ($duration !== null) {
            $this->due = null;
        }

        return $this;
    }

    public function setCompleted(DateTimeInterface|string|int|null $completed): self
    {
        $this->completed = $completed === null ? null : DateTimeNormalizer::normalize($completed);

        return $this;
    }

    public function getCompleted(): ?DateTimeImmutable
    {
        return $this->completed;
    }

    public function setPercentComplete(?int $percent): self
    {
        if ($percent !== null && ($percent < 0 || $percent > 100)) {
            throw new InvalidArgumentException('PERCENT-COMPLETE must be between 0 and 100.');
        }
        $this->percentComplete = $percent;

        return $this;
    }

    public function setStatus(?TodoStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?TodoStatus
    {
        return $this->status;
    }

    /**
     * Marks the to-do as done: status COMPLETED, 100 percent, completed now or at the given time.
     */
    public function markCompleted(DateTimeInterface|string|int|null $at = null): self
    {
        return $this
            ->setStatus(TodoStatus::Completed)
            ->setPercentComplete(100)
            ->setCompleted($at ?? new DateTimeImmutable());
    }

    protected function validate(): void
    {
        parent::validate();

        $start = $this->getStart();
        if ($this->duration !== null && $start === null) {
            throw new ValidationException(sprintf('To-do "%s" needs a start date to use a duration.', $this->getUid()));
        }
        if ($this->due !== null && $start !== null && $this->due < $start) {
            throw new ValidationException(sprintf('To-do "%s" is due before it starts.', $this->getUid()));
        }
    }

    protected function buildTimeProperties(): iterable
    {
        $start = $this->getStart();
        if ($start !== null) {
            yield $this->dateProperty('DTSTART', $start);
        }
        if ($this->due !== null) {
            yield $this->dateProperty('DUE', $this->due);
        } elseif ($this->duration !== null) {
            yield new Property('DURATION', $this->duration->toString());
        }
        if ($this->completed !== null) {
            yield new Property('COMPLETED', Formatter::formatDateTime($this->completed));
        }
    }

    protected function buildStatusProperties(): iterable
    {
        if ($this->percentComplete !== null) {
            yield new Property('PERCENT-COMPLETE', (string) $this->percentComplete);
        }
        if ($this->status !== null) {
            yield new Property('STATUS', $this->status->value);
        }
    }
}
