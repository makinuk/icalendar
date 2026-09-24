<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * ICalendar::convertUTC($timestamp) becomes $timestamp: 3.0 converts every date to UTC itself.
 */
final class ConvertUtcRector extends AbstractLegacyRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Removes ICalendar::convertUTC(); dates are converted to UTC by the library', [
            new CodeSample('$event->setStartDate(ICalendar::convertUTC($start));', '$event->setStart($start);'),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'convertUTC') || !$this->isName($node->class, LegacyClass::CALENDAR)) {
            return null;
        }

        $values = $this->positionalValues($node->args);
        if ($values === null || count($values) > 1) {
            return null;
        }

        return $values[0] ?? new FuncCall(new Name('time'));
    }
}
