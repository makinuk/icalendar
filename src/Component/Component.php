<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Component;

use Makinuk\ICalendar\Property\Property;
use Makinuk\ICalendar\Support\Formatter;
use Stringable;

/**
 * Base class of every BEGIN:...END: block (RFC 5545 §3.6).
 *
 * To add a new component type, extend this class (or CalendarComponent for
 * schedulable components), return its name from getComponentName() and yield
 * its properties from buildProperties().
 */
abstract class Component implements Stringable
{
    /** @var list<Property> */
    private array $extraProperties = [];

    /**
     * The component name, e.g. "VEVENT".
     */
    abstract public function getComponentName(): string;

    /**
     * @return iterable<Property>
     */
    abstract protected function buildProperties(): iterable;

    /**
     * @return iterable<Component>
     */
    protected function buildChildren(): iterable
    {
        return [];
    }

    /**
     * Throws a ValidationException when the component cannot be rendered.
     */
    protected function validate(): void {}

    /**
     * Adds a property that has no dedicated setter, e.g. an X- extension.
     */
    public function addProperty(Property $property): static
    {
        $this->extraProperties[] = $property;

        return $this;
    }

    /**
     * Renders the component as folded content lines terminated by CRLF.
     */
    public function render(): string
    {
        $this->validate();

        $output = 'BEGIN:' . $this->getComponentName() . Formatter::CRLF;
        foreach ($this->buildProperties() as $property) {
            $output .= $property->render() . Formatter::CRLF;
        }
        foreach ($this->extraProperties as $property) {
            $output .= $property->render() . Formatter::CRLF;
        }
        foreach ($this->buildChildren() as $child) {
            $output .= $child->render();
        }

        return $output . 'END:' . $this->getComponentName() . Formatter::CRLF;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
