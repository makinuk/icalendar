<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Tests\Property;

use Makinuk\ICalendar\Enum\ParticipationStatus;
use Makinuk\ICalendar\Enum\Role;
use Makinuk\ICalendar\Exception\InvalidArgumentException;
use Makinuk\ICalendar\Property\Attendee;
use Makinuk\ICalendar\Property\Organizer;
use Makinuk\ICalendar\Property\Property;
use Makinuk\ICalendar\Tests\TestCase;

final class PropertyTest extends TestCase
{
    public function testRendersNameParametersAndValue(): void
    {
        $property = new Property('x-custom', 'value', ['x-param' => 'a', 'multi' => ['b', 'c']]);

        self::assertSame('X-CUSTOM;X-PARAM=a;MULTI=b,c:value', $property->toString());
    }

    public function testTextEscapesTheValue(): void
    {
        self::assertSame('SUMMARY:a\\, b', Property::text('SUMMARY', 'a, b')->toString());
    }

    public function testRejectsInvalidNames(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Property('BAD NAME', 'x');
    }

    public function testRejectsLineBreaksInRawValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Property('X-FOO', "a\r\nX-INJECTED:1");
    }

    public function testOrganizer(): void
    {
        $organizer = new Organizer('mailto:jane@example.com', 'Jane Doe');

        self::assertSame('jane@example.com', $organizer->getEmail());
        self::assertSame('ORGANIZER;CN=Jane Doe:mailto:jane@example.com', $organizer->toProperty()->toString());
    }

    public function testOrganizerWithoutName(): void
    {
        self::assertSame('ORGANIZER:mailto:jane@example.com', (new Organizer('jane@example.com'))->toProperty()->toString());
    }

    public function testAttendee(): void
    {
        $attendee = new Attendee('bob@example.com', 'Doe, Bob', Role::RequiredParticipant, ParticipationStatus::NeedsAction, true);

        self::assertSame(
            'ATTENDEE;CN="Doe, Bob";ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE:mailto:bob@example.com',
            $attendee->toProperty()->toString(),
        );
    }

    public function testRejectsInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Attendee('not an email');
    }
}
