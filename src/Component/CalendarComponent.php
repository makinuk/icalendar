<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Component;

use DateTimeImmutable;
use DateTimeInterface;
use Makinuk\ICalendar\Enum\Classification;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Property\Attendee;
use Makinuk\ICalendar\Property\Organizer;
use Makinuk\ICalendar\Property\Property;
use Makinuk\ICalendar\Support\DateTimeNormalizer;
use Makinuk\ICalendar\Support\Formatter;
use Makinuk\ICalendar\Support\Uid;
use Makinuk\ICalendar\Value\RecurrenceRule;

/**
 * Properties shared by the schedulable components VEVENT and VTODO.
 *
 * Every setter accepting a date takes a DateTimeInterface, any string understood by
 * DateTimeImmutable ("2026-10-01 09:00", "+1 day") or a Unix timestamp. Date-times are
 * written in UTC; all-day components are written as DATE values.
 */
abstract class CalendarComponent extends Component
{
    private string $uid;
    private DateTimeImmutable $timestamp;
    private ?DateTimeImmutable $created = null;
    private ?DateTimeImmutable $lastModified = null;
    private ?DateTimeImmutable $start = null;
    private bool $allDay = false;
    private ?string $summary = null;
    private ?string $description = null;
    private ?string $location = null;
    private ?string $url = null;
    private ?float $latitude = null;
    private ?float $longitude = null;
    private ?Classification $classification = null;
    private ?int $priority = null;
    private ?int $sequence = null;
    private ?Organizer $organizer = null;
    private ?string $color = null;
    private ?RecurrenceRule $recurrenceRule = null;

    /** @var list<Attendee> */
    private array $attendees = [];

    /** @var list<string> */
    private array $categories = [];

    /** @var list<Alarm> */
    private array $alarms = [];

    /** @var list<DateTimeImmutable> */
    private array $exceptionDates = [];

    /** @var list<array{uri: string, mimeType: ?string}> */
    private array $attachments = [];

    /**
     * @param string|null $uid a globally unique identifier; a UUID is generated when omitted
     */
    public function __construct(?string $uid = null)
    {
        $this->setUid($uid ?? Uid::generate());
        $this->timestamp = new DateTimeImmutable();
    }

    public function setUid(string $uid): static
    {
        if (trim($uid) === '') {
            throw new InvalidArgumentException('UID must not be empty.');
        }
        $this->uid = $uid;

        return $this;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * Sets DTSTAMP. Defaults to the moment the object was created.
     */
    public function setTimestamp(DateTimeInterface|string|int $timestamp): static
    {
        $this->timestamp = DateTimeNormalizer::normalize($timestamp);

        return $this;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setCreated(DateTimeInterface|string|int|null $created): static
    {
        $this->created = $created === null ? null : DateTimeNormalizer::normalize($created);

        return $this;
    }

    public function setLastModified(DateTimeInterface|string|int|null $lastModified): static
    {
        $this->lastModified = $lastModified === null ? null : DateTimeNormalizer::normalize($lastModified);

        return $this;
    }

    public function setStart(DateTimeInterface|string|int|null $start): static
    {
        $this->start = $start === null ? null : DateTimeNormalizer::normalize($start);

        return $this;
    }

    public function getStart(): ?DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * Writes the dates of this component as DATE values without a time of day.
     */
    public function setAllDay(bool $allDay = true): static
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function isAllDay(): bool
    {
        return $this->allDay;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setUrl(?string $url): static
    {
        if ($url !== null) {
            self::assertUri($url);
        }
        $this->url = $url;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Sets the GEO position in decimal degrees.
     */
    public function setGeo(float $latitude, float $longitude): static
    {
        if (abs($latitude) > 90 || abs($longitude) > 180) {
            throw new InvalidArgumentException('Latitude must be within ±90 and longitude within ±180 degrees.');
        }
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        return $this;
    }

    public function setClassification(?Classification $classification): static
    {
        $this->classification = $classification;

        return $this;
    }

    /**
     * Sets PRIORITY from 1 (highest) to 9 (lowest); 0 means undefined.
     */
    public function setPriority(?int $priority): static
    {
        if ($priority !== null && ($priority < 0 || $priority > 9)) {
            throw new InvalidArgumentException('PRIORITY must be between 0 and 9.');
        }
        $this->priority = $priority;

        return $this;
    }

    /**
     * Sets SEQUENCE; increment it every time you send an update of an invitation.
     */
    public function setSequence(?int $sequence): static
    {
        if ($sequence !== null && $sequence < 0) {
            throw new InvalidArgumentException('SEQUENCE must not be negative.');
        }
        $this->sequence = $sequence;

        return $this;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setOrganizer(Organizer|string|null $organizer, ?string $name = null): static
    {
        $this->organizer = is_string($organizer) ? new Organizer($organizer, $name) : $organizer;

        return $this;
    }

    public function getOrganizer(): ?Organizer
    {
        return $this->organizer;
    }

    public function addAttendee(Attendee|string $attendee, ?string $name = null): static
    {
        $this->attendees[] = is_string($attendee) ? new Attendee($attendee, $name) : $attendee;

        return $this;
    }

    /**
     * @return list<Attendee>
     */
    public function getAttendees(): array
    {
        return $this->attendees;
    }

    public function addCategory(string ...$categories): static
    {
        foreach ($categories as $category) {
            $this->categories[] = $category;
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Sets COLOR to a CSS3 color name such as "turquoise" (RFC 7986 §5.9).
     */
    public function setColor(?string $color): static
    {
        if ($color !== null && preg_match('/^[A-Za-z]+$/', $color) !== 1) {
            throw new InvalidArgumentException('COLOR must be a CSS3 color name, e.g. "red".');
        }
        $this->color = $color;

        return $this;
    }

    public function addAlarm(Alarm $alarm): static
    {
        $this->alarms[] = $alarm;

        return $this;
    }

    /**
     * @return list<Alarm>
     */
    public function getAlarms(): array
    {
        return $this->alarms;
    }

    public function setRecurrenceRule(?RecurrenceRule $rule): static
    {
        $this->recurrenceRule = $rule;

        return $this;
    }

    public function getRecurrenceRule(): ?RecurrenceRule
    {
        return $this->recurrenceRule;
    }

    /**
     * Excludes occurrences of the recurrence rule (EXDATE).
     */
    public function addExceptionDate(DateTimeInterface|string|int ...$dates): static
    {
        foreach ($dates as $date) {
            $this->exceptionDates[] = DateTimeNormalizer::normalize($date);
        }

        return $this;
    }

    /**
     * Attaches a document by URI (ATTACH).
     */
    public function addAttachment(string $uri, ?string $mimeType = null): static
    {
        self::assertUri($uri);
        $this->attachments[] = ['uri' => $uri, 'mimeType' => $mimeType];

        return $this;
    }

    /**
     * Yields the properties describing when the component takes place (DTSTART, DTEND, DUE...).
     *
     * @return iterable<Property>
     */
    abstract protected function buildTimeProperties(): iterable;

    /**
     * Yields the properties specific to the component type (STATUS, TRANSP...).
     *
     * @return iterable<Property>
     */
    protected function buildStatusProperties(): iterable
    {
        return [];
    }

    protected function validate(): void
    {
        if ($this->start === null && ($this->recurrenceRule !== null || $this->exceptionDates !== [])) {
            throw new ValidationException(sprintf(
                '%s "%s" needs a start date to use a recurrence rule or exception dates.',
                $this->getComponentName(),
                $this->uid,
            ));
        }
    }

    protected function buildProperties(): iterable
    {
        yield Property::text('UID', $this->uid);
        yield new Property('DTSTAMP', Formatter::formatDateTime($this->timestamp));
        if ($this->created !== null) {
            yield new Property('CREATED', Formatter::formatDateTime($this->created));
        }
        if ($this->lastModified !== null) {
            yield new Property('LAST-MODIFIED', Formatter::formatDateTime($this->lastModified));
        }

        yield from $this->buildTimeProperties();

        if ($this->summary !== null) {
            yield Property::text('SUMMARY', $this->summary);
        }
        if ($this->description !== null) {
            yield Property::text('DESCRIPTION', $this->description);
        }
        if ($this->location !== null) {
            yield Property::text('LOCATION', $this->location);
        }
        if ($this->latitude !== null && $this->longitude !== null) {
            yield new Property('GEO', self::formatFloat($this->latitude) . ';' . self::formatFloat($this->longitude));
        }
        if ($this->url !== null) {
            yield new Property('URL', $this->url);
        }
        if ($this->organizer !== null) {
            yield $this->organizer->toProperty();
        }
        foreach ($this->attendees as $attendee) {
            yield $attendee->toProperty();
        }
        if ($this->categories !== []) {
            yield new Property('CATEGORIES', implode(',', array_map(Formatter::escapeText(...), $this->categories)));
        }
        if ($this->classification !== null) {
            yield new Property('CLASS', $this->classification->value);
        }
        if ($this->priority !== null) {
            yield new Property('PRIORITY', (string) $this->priority);
        }
        if ($this->sequence !== null) {
            yield new Property('SEQUENCE', (string) $this->sequence);
        }

        yield from $this->buildStatusProperties();

        if ($this->color !== null) {
            yield new Property('COLOR', $this->color);
        }
        if ($this->recurrenceRule !== null) {
            yield new Property('RRULE', $this->recurrenceRule->toString($this->allDay));
        }
        if ($this->exceptionDates !== []) {
            yield $this->allDay
                ? new Property('EXDATE', implode(',', array_map(Formatter::formatDate(...), $this->exceptionDates)), ['VALUE' => 'DATE'])
                : new Property('EXDATE', implode(',', array_map(Formatter::formatDateTime(...), $this->exceptionDates)));
        }
        foreach ($this->attachments as $attachment) {
            yield new Property(
                'ATTACH',
                $attachment['uri'],
                $attachment['mimeType'] !== null ? ['FMTTYPE' => $attachment['mimeType']] : [],
            );
        }
    }

    protected function buildChildren(): iterable
    {
        return $this->alarms;
    }

    /**
     * Creates a DATE or DATE-TIME property depending on isAllDay().
     */
    protected function dateProperty(string $name, DateTimeImmutable $value): Property
    {
        return $this->allDay
            ? new Property($name, Formatter::formatDate($value), ['VALUE' => 'DATE'])
            : new Property($name, Formatter::formatDateTime($value));
    }

    private static function assertUri(string $uri): void
    {
        if ($uri === '' || preg_match('/[\s\x00-\x1F\x7F]/', $uri) === 1) {
            throw new InvalidArgumentException(sprintf('Invalid URI "%s".', $uri));
        }
    }

    private static function formatFloat(float $value): string
    {
        return rtrim(rtrim(sprintf('%.6F', $value), '0'), '.');
    }
}
