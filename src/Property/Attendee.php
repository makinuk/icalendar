<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Property;

use Makinuk\ICalendar\Enum\ParticipationStatus;
use Makinuk\ICalendar\Enum\Role;

/**
 * An ATTENDEE of an event or to-do, or a recipient of an e-mail alarm (RFC 5545 §3.8.4.1).
 */
final class Attendee extends CalendarUser
{
    public function __construct(
        string $email,
        ?string $name = null,
        private ?Role $role = null,
        private ?ParticipationStatus $status = null,
        private bool $rsvp = false,
    ) {
        parent::__construct($email, $name);
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setStatus(?ParticipationStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?ParticipationStatus
    {
        return $this->status;
    }

    /**
     * Asks the attendee to reply to the invitation.
     */
    public function setRsvp(bool $rsvp = true): self
    {
        $this->rsvp = $rsvp;

        return $this;
    }

    public function isRsvp(): bool
    {
        return $this->rsvp;
    }

    public function toProperty(): Property
    {
        $parameters = [];
        if ($this->role !== null) {
            $parameters['ROLE'] = $this->role->value;
        }
        if ($this->status !== null) {
            $parameters['PARTSTAT'] = $this->status->value;
        }
        if ($this->rsvp) {
            $parameters['RSVP'] = 'TRUE';
        }

        return $this->createProperty('ATTENDEE', $parameters);
    }
}
