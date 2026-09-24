<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Component\Event;
use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Turns the positional arguments of the 2.x event constructor into setter calls.
 */
final class NewEventRector extends AbstractLegacyRector
{
    /** Setter of each constructor argument after the UID, in the order of the 2.x constructor. */
    private const SETTERS = ['setStart', 'setEnd', 'addAlarm', 'setLocation', 'setOrganizer', 'setDescription', 'setSummary'];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces the positional arguments of the 2.x event constructor with setters', [
            new CodeSample(
                "new ICalEvent('uid-1', \$start, \$end, null, 'Istanbul');",
                "(new Event('uid-1'))->setStart(\$start)->setEnd(\$end)->setLocation('Istanbul');",
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
        if (!$this->isName($node->class, LegacyClass::EVENT)) {
            return null;
        }

        $values = $this->positionalValues($node->args);
        if ($values === null || $values === []) {
            return null;
        }

        $uid = array_shift($values);
        $setters = array_filter(
            array_combine(array_slice(self::SETTERS, 0, count($values)), $values),
            fn(Expr $value): bool => !$this->isNullLiteral($value),
        );

        if ($setters === [] && !$this->isNullLiteral($uid)) {
            return null;
        }

        $event = new New_(new FullyQualified(Event::class), $this->isNullLiteral($uid) ? [] : [new Arg($uid)]);
        foreach ($setters as $setter => $value) {
            $event = new MethodCall($event, $setter, [new Arg($value)]);
        }

        return $event;
    }
}
