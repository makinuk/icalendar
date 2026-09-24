<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * $calendar->setMethod('REQUEST') becomes $calendar->setMethod(Method::Request).
 */
final class SetMethodToEnumRector extends AbstractLegacyRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces the method string with the Method enum', [
            new CodeSample("\$calendar->setMethod('REQUEST');", '$calendar->setMethod(Method::Request);'),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'setMethod') || !$this->isInstanceOf($node->var, LegacyClass::CALENDAR)) {
            return null;
        }

        $values = $this->positionalValues($node->args);
        if ($values === null || count($values) !== 1 || $this->isMethodEnum($values[0])) {
            return null;
        }

        $node->args = [new Arg($this->methodEnum($values[0]))];

        return $node;
    }
}
