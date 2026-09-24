<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Calendar;
use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * new ICalendar($event, 'REQUEST') becomes (new ICalendar($event))->setMethod(Method::Request).
 */
final class NewCalendarRector extends AbstractLegacyRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Moves the method argument of the 2.x calendar constructor to setMethod()', [
            new CodeSample(
                "new ICalendar(\$event, 'REQUEST');",
                '(new Calendar($event))->setMethod(Method::Request);',
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
        if (!$this->isName($node->class, LegacyClass::CALENDAR)) {
            return null;
        }

        $values = $this->positionalValues($node->args);
        if ($values === null || $values === []) {
            return null;
        }

        $event = $values[0];
        $method = $values[1] ?? null;
        if ($method === null && !$this->isNullLiteral($event)) {
            return null;
        }

        $calendar = new New_(new FullyQualified(Calendar::class), $this->isNullLiteral($event) ? [] : [new Arg($event)]);

        return $method === null
            ? $calendar
            : new MethodCall($calendar, 'setMethod', [new Arg($this->methodEnum($method))]);
    }
}
