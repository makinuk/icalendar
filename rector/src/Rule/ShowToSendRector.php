<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * $calendar->show('a.ics') becomes $calendar->send('a.ics', asAttachment: false), keeping the
 * inline Content-Disposition of 2.x.
 */
final class ShowToSendRector extends AbstractLegacyRector
{
    private const LEGACY_DEFAULT_FILENAME = 'iCalendar.ics';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces show() with send() and keeps the inline disposition', [
            new CodeSample("\$calendar->show('event.ics');", "\$calendar->send('event.ics', asAttachment: false);"),
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
        if (!$this->isName($node->name, 'show') || !$this->isInstanceOf($node->var, LegacyClass::CALENDAR)) {
            return null;
        }

        $values = $this->positionalValues($node->args);
        if ($values === null || count($values) > 1) {
            return null;
        }

        $node->name = new Identifier('send');
        $node->args = [
            new Arg($values[0] ?? new String_(self::LEGACY_DEFAULT_FILENAME)),
            $this->namedArg('asAttachment', new ConstFetch(new Name('false'))),
        ];

        return $node;
    }
}
