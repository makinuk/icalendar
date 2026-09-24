<?php

declare(strict_types=1);

namespace Makinuk\ICalendar;

use DateInterval;
use Makinuk\ICalendar\Component\CalendarComponent;
use Makinuk\ICalendar\Component\Component;
use Makinuk\ICalendar\Enum\Method;
use Makinuk\ICalendar\Exception\IOException;
use Makinuk\ICalendar\Exception\ValidationException;
use Makinuk\ICalendar\Property\Property;
use Makinuk\ICalendar\Value\Duration;

/**
 * The VCALENDAR object: a container of events and to-dos (RFC 5545 §3.4).
 *
 *     $calendar = (new Calendar())->add($event);
 *     file_put_contents('meeting.ics', $calendar->render());
 */
final class Calendar extends Component
{
    public const DEFAULT_PRODUCT_ID = '-//makinuk//iCalendar 3.0//EN';

    private string $productId = self::DEFAULT_PRODUCT_ID;
    private ?Method $method = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?Duration $refreshInterval = null;

    /** @var list<CalendarComponent> */
    private array $components = [];

    public function __construct(CalendarComponent ...$components)
    {
        $this->add(...$components);
    }

    public function getComponentName(): string
    {
        return 'VCALENDAR';
    }

    public function add(CalendarComponent ...$components): self
    {
        foreach ($components as $component) {
            $this->components[] = $component;
        }

        return $this;
    }

    /**
     * @return list<CalendarComponent>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Identifies the application that produced the calendar, e.g. "-//Acme//Booking 1.0//EN".
     */
    public function setProductId(string $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Sets the iTIP METHOD. Use Method::Request for invitations sent by e-mail and
     * Method::Cancel to cancel them; leave it empty for calendar feeds and downloads.
     */
    public function setMethod(?Method $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): ?Method
    {
        return $this->method;
    }

    /**
     * Sets the display name of the calendar (NAME and X-WR-CALNAME).
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the description of the calendar (DESCRIPTION and X-WR-CALDESC).
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Suggests how often subscribers should poll a calendar feed (REFRESH-INTERVAL and X-PUBLISHED-TTL).
     */
    public function setRefreshInterval(Duration|DateInterval|null $interval): self
    {
        $this->refreshInterval = $interval instanceof DateInterval ? Duration::fromDateInterval($interval) : $interval;

        return $this;
    }

    /**
     * The value for the Content-Type header, including the iTIP method when set.
     */
    public function getContentType(): string
    {
        return 'text/calendar; charset=utf-8' . ($this->method !== null ? '; method=' . $this->method->value : '');
    }

    /**
     * Writes the calendar to a file.
     */
    public function save(string $path): void
    {
        $content = $this->render();

        if (@file_put_contents($path, $content) !== strlen($content)) {
            throw new IOException(sprintf('Could not write the calendar to "%s".', $path));
        }
    }

    /**
     * Sends the calendar to the browser with the matching HTTP headers.
     *
     * In a framework, prefer returning render() and getContentType() from a response object.
     */
    public function send(string $filename = 'calendar.ics', bool $asAttachment = true): void
    {
        $content = $this->render();
        $filename = str_replace(['"', '\\', "\r", "\n"], '', $filename);

        header('Content-Type: ' . $this->getContentType());
        header(sprintf('Content-Disposition: %s; filename="%s"', $asAttachment ? 'attachment' : 'inline', $filename));
        header('Content-Length: ' . strlen($content));

        echo $content;
    }

    protected function validate(): void
    {
        if ($this->components === []) {
            throw new ValidationException('A calendar needs at least one event or to-do.');
        }
    }

    protected function buildProperties(): iterable
    {
        yield Property::text('PRODID', $this->productId);
        yield new Property('VERSION', '2.0');
        yield new Property('CALSCALE', 'GREGORIAN');
        if ($this->method !== null) {
            yield new Property('METHOD', $this->method->value);
        }
        if ($this->name !== null) {
            yield Property::text('NAME', $this->name);
            yield Property::text('X-WR-CALNAME', $this->name);
        }
        if ($this->description !== null) {
            yield Property::text('DESCRIPTION', $this->description);
            yield Property::text('X-WR-CALDESC', $this->description);
        }
        if ($this->refreshInterval !== null) {
            yield new Property('REFRESH-INTERVAL', $this->refreshInterval->toString(), ['VALUE' => 'DURATION']);
            yield new Property('X-PUBLISHED-TTL', $this->refreshInterval->toString());
        }
    }

    protected function buildChildren(): iterable
    {
        return $this->components;
    }
}
