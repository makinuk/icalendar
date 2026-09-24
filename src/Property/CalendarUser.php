<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Property;

use Makinuk\ICalendar\Exception\InvalidArgumentException;

/**
 * A person addressed by e-mail (CAL-ADDRESS, RFC 5545 §3.3.3).
 */
abstract class CalendarUser
{
    private readonly string $email;

    public function __construct(string $email, private readonly ?string $name = null)
    {
        $email = trim((string) preg_replace('/^mailto:/i', '', trim($email)));

        if (preg_match('/^[^@\s]+@[^@\s]+$/u', $email) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid e-mail address "%s".', $email));
        }

        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    abstract public function toProperty(): Property;

    /**
     * @param array<string, string> $parameters
     */
    protected function createProperty(string $propertyName, array $parameters = []): Property
    {
        if ($this->name !== null && $this->name !== '') {
            $parameters = ['CN' => $this->name] + $parameters;
        }

        return new Property($propertyName, 'mailto:' . $this->email, $parameters);
    }
}
