<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Component\Alarm;
use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * new ICalAlarm($day, $hour, $minute, $second) becomes
 * Alarm::display('Reminder')->before(days: $day, hours: $hour, minutes: $minute, seconds: $second).
 */
final class NewAlarmRector extends AbstractLegacyRector
{
    /** The description written by 2.x. */
    private const LEGACY_DESCRIPTION = 'Reminder';

    private const PARTS = ['days', 'hours', 'minutes', 'seconds'];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces the 2.x alarm constructor with a display alarm', [
            new CodeSample('new ICalAlarm(0, 1, 10, 0);', "Alarm::display('Reminder')->before(hours: 1, minutes: 10);"),
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
        if (!$this->isName($node->class, LegacyClass::ALARM)) {
            return null;
        }

        $values = $this->positionalValues($node->args);
        if ($values === null || count($values) > count(self::PARTS)) {
            return null;
        }

        $args = [];
        foreach ($values as $position => $value) {
            if (!$this->isZeroLiteral($value)) {
                $args[] = $this->namedArg(self::PARTS[$position], $value);
            }
        }

        return new MethodCall(
            new StaticCall(new FullyQualified(Alarm::class), 'display', [new Arg(new String_(self::LEGACY_DESCRIPTION))]),
            'before',
            $args,
        );
    }
}
