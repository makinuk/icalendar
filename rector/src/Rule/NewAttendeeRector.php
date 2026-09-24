<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Property\Attendee;
use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * new ICalAttendee(new ICalPerson($name, $email), true) becomes new Attendee($email, $name, rsvp: true).
 */
final class NewAttendeeRector extends AbstractLegacyRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces the person argument of the 2.x attendee constructor with e-mail and name', [
            new CodeSample(
                "new ICalAttendee(new ICalPerson('Bob', 'bob@example.com'), true);",
                "new Attendee('bob@example.com', 'Bob', rsvp: true);",
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->class, LegacyClass::ATTENDEE)) {
            return null;
        }

        $values = $this->positionalValues($node->args);
        if ($values === null || $values === [] || count($values) > 2) {
            return null;
        }

        $person = $values[0];
        $reply = $values[1] ?? null;

        if ($person instanceof New_ && $this->isName($person->class, LegacyClass::PERSON)) {
            $personValues = $this->positionalValues($person->args);
            if ($personValues === null || count($personValues) !== 2) {
                return null;
            }
            $args = [new Arg($personValues[1]), new Arg($personValues[0])];
        } else {
            $args = [new Arg(new MethodCall($person, 'getEmail')), new Arg(new MethodCall($person, 'getName'))];
        }

        if ($reply !== null && !$this->isFalseLiteral($reply)) {
            $args[] = $this->namedArg('rsvp', $reply);
        }

        return new New_(new FullyQualified(Attendee::class), $args);
    }
}
