<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Property\Organizer;
use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * new ICalPerson($name, $email) becomes new Organizer($email, $name).
 */
final class NewPersonRector extends AbstractLegacyRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Swaps the name and e-mail arguments of the 2.x person constructor', [
            new CodeSample(
                "new ICalPerson('Jane Doe', 'jane@example.com');",
                "new Organizer('jane@example.com', 'Jane Doe');",
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
        if (!$this->isName($node->class, LegacyClass::PERSON)) {
            return null;
        }

        $values = $this->positionalValues($node->args);
        if ($values === null || count($values) !== 2) {
            return null;
        }

        return new New_(new FullyQualified(Organizer::class), [new Arg($values[1]), new Arg($values[0])]);
    }
}
