<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Component;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Makinuk\ICalendar\Enum\AlarmAction;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Property\Attendee;
use Makinuk\ICalendar\Property\Property;
use Makinuk\ICalendar\Support\DateTimeNormalizer;
use Makinuk\ICalendar\Support\Formatter;
use Makinuk\ICalendar\Value\Duration;

/**
 * A VALARM reminder attached to an event or to-do (RFC 5545 §3.6.6).
 *
 *     Alarm::display('Stand-up')->before(minutes: 10);
 *     Alarm::email('Tomorrow: kickoff', 'Agenda attached', 'team@example.com')->before(days: 1);
 *     Alarm::audio()->at('2026-10-01 08:55');
 *
 * Without a trigger the alarm fires 15 minutes before the start.
 */
final class Alarm extends Component
{
    private Duration|DateTimeImmutable $trigger;
    private bool $relativeToEnd = false;
    private ?string $description = null;
    private ?string $summary = null;
    private ?string $attachment = null;
    private ?int $repeat = null;
    private ?Duration $repeatInterval = null;

    /** @var list<Attendee> */
    private array $recipients = [];

    private function __construct(private readonly AlarmAction $action)
    {
        $this->trigger = Duration::minutes(15)->negate();
    }

    /**
     * Shows a notification with the given text.
     */
    public static function display(string $description = 'Reminder'): self
    {
        $alarm = new self(AlarmAction::Display);
        $alarm->description = $description;

        return $alarm;
    }

    /**
     * Plays a sound; the client's default sound is used when no URI is given.
     */
    public static function audio(?string $soundUri = null): self
    {
        $alarm = new self(AlarmAction::Audio);
        $alarm->attachment = $soundUri;

        return $alarm;
    }

    /**
     * Sends an e-mail to one or more recipients.
     */
    public static function email(string $subject, string $body, Attendee|string ...$recipients): self
    {
        $alarm = new self(AlarmAction::Email);
        $alarm->summary = $subject;
        $alarm->description = $body;
        foreach ($recipients as $recipient) {
            $alarm->recipients[] = is_string($recipient) ? new Attendee($recipient) : $recipient;
        }

        return $alarm;
    }

    public function getComponentName(): string
    {
        return 'VALARM';
    }

    public function getAction(): AlarmAction
    {
        return $this->action;
    }

    /**
     * Triggers the given amount of time before the start (or end, see relativeToEnd()).
     */
    public function before(int $weeks = 0, int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): self
    {
        return $this->setTrigger(Duration::of($weeks, $days, $hours, $minutes, $seconds)->negate());
    }

    /**
     * Triggers the given amount of time after the start (or end, see relativeToEnd()).
     */
    public function after(int $weeks = 0, int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): self
    {
        return $this->setTrigger(Duration::of($weeks, $days, $hours, $minutes, $seconds));
    }

    /**
     * Triggers at an absolute point in time.
     */
    public function at(DateTimeInterface|string|int $moment): self
    {
        return $this->setTrigger(DateTimeNormalizer::normalize($moment));
    }

    /**
     * Sets the trigger directly; a relative trigger is measured from the start of the parent.
     */
    public function setTrigger(Duration|DateInterval|DateTimeInterface $trigger): self
    {
        $this->trigger = match (true) {
            $trigger instanceof DateInterval => Duration::fromDateInterval($trigger),
            $trigger instanceof DateTimeInterface => DateTimeNormalizer::normalize($trigger),
            default => $trigger,
        };

        return $this;
    }

    /**
     * Measures a relative trigger from the end of the event / due date of the to-do.
     */
    public function relativeToEnd(bool $relativeToEnd = true): self
    {
        $this->relativeToEnd = $relativeToEnd;

        return $this;
    }

    /**
     * Repeats the alarm the given number of additional times.
     */
    public function repeat(int $times, Duration|DateInterval $interval): self
    {
        if ($times < 1) {
            throw new InvalidArgumentException('An alarm must repeat at least once.');
        }
        $this->repeat = $times;
        $this->repeatInterval = $interval instanceof DateInterval ? Duration::fromDateInterval($interval) : $interval;

        return $this;
    }

    protected function validate(): void
    {
        if ($this->action !== AlarmAction::Audio && ($this->description === null || trim($this->description) === '')) {
            throw new ValidationException(sprintf('A %s alarm needs a description.', $this->action->value));
        }
        if ($this->action === AlarmAction::Email) {
            if ($this->summary === null || trim($this->summary) === '') {
                throw new ValidationException('An EMAIL alarm needs a subject.');
            }
            if ($this->recipients === []) {
                throw new ValidationException('An EMAIL alarm needs at least one recipient.');
            }
        }
    }

    protected function buildProperties(): iterable
    {
        yield new Property('ACTION', $this->action->value);

        if ($this->trigger instanceof DateTimeImmutable) {
            yield new Property('TRIGGER', Formatter::formatDateTime($this->trigger), ['VALUE' => 'DATE-TIME']);
        } else {
            yield new Property('TRIGGER', $this->trigger->toString(), $this->relativeToEnd ? ['RELATED' => 'END'] : []);
        }

        if ($this->description !== null && $this->action !== AlarmAction::Audio) {
            yield Property::text('DESCRIPTION', $this->description);
        }
        if ($this->summary !== null) {
            yield Property::text('SUMMARY', $this->summary);
        }
        foreach ($this->recipients as $recipient) {
            yield $recipient->toProperty();
        }
        if ($this->attachment !== null) {
            yield new Property('ATTACH', $this->attachment);
        }
        if ($this->repeat !== null && $this->repeatInterval !== null) {
            yield new Property('REPEAT', (string) $this->repeat);
            yield new Property('DURATION', $this->repeatInterval->toString());
        }
    }
}
