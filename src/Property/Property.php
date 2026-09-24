<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Property;

use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Support\Formatter;

/**
 * A single content line: NAME;PARAM=value:VALUE (RFC 5545 §3.1).
 *
 * Use it to add properties the typed API does not cover yet, e.g. X- extensions:
 *
 *     $event->addProperty(Property::text('X-MICROSOFT-CDO-BUSYSTATUS', 'BUSY'));
 */
final class Property
{
    private const NAME_PATTERN = '/^[A-Za-z0-9-]+$/';

    private readonly string $name;

    /** @var array<string, list<string>> */
    private array $parameters = [];

    /**
     * @param string                             $name       property name, e.g. "X-FOO"
     * @param string                             $value      the value exactly as it must appear in the output
     * @param array<string, string|list<string>> $parameters property parameters keyed by name
     */
    public function __construct(string $name, private readonly string $value, array $parameters = [])
    {
        $this->name = self::normalizeName($name);

        if (preg_match('/[\r\n]/', $value) === 1) {
            throw new InvalidArgumentException(sprintf(
                'The value of property "%s" must not contain line breaks; use Property::text() for text values.',
                $this->name,
            ));
        }

        foreach ($parameters as $parameterName => $parameterValue) {
            $this->parameters[self::normalizeName($parameterName)] = is_array($parameterValue)
                ? $parameterValue
                : [$parameterValue];
        }
    }

    /**
     * Creates a property whose value is TEXT and must be escaped (RFC 5545 §3.3.11).
     *
     * @param array<string, string|list<string>> $parameters
     */
    public static function text(string $name, string $value, array $parameters = []): self
    {
        return new self($name, Formatter::escapeText($value), $parameters);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns the unfolded content line, without line break.
     */
    public function toString(): string
    {
        $line = $this->name;
        foreach ($this->parameters as $name => $values) {
            $line .= ';' . $name . '=' . implode(',', array_map(Formatter::formatParameterValue(...), $values));
        }

        return $line . ':' . $this->value;
    }

    /**
     * Returns the folded content line, without trailing line break.
     */
    public function render(): string
    {
        return Formatter::fold($this->toString());
    }

    private static function normalizeName(string $name): string
    {
        if (preg_match(self::NAME_PATTERN, $name) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid property or parameter name "%s".', $name));
        }

        return strtoupper($name);
    }
}
