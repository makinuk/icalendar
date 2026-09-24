<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Rector\LegacyClass;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * $person->Name and $person->Email become $person->getName() and $person->getEmail().
 */
final class PersonPropertyToGetterRector extends AbstractLegacyRector
{
    private const GETTERS = ['Name' => 'getName', 'Email' => 'getEmail'];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces reads of the public 2.x person properties with getters', [
            new CodeSample('echo $person->Email;', 'echo $person->getEmail();'),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [PropertyFetch::class];
    }

    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getAttribute(AttributeKey::IS_BEING_ASSIGNED) === true) {
            return null;
        }

        $name = $this->getName($node->name);
        if ($name === null || !isset(self::GETTERS[$name]) || !$this->isInstanceOf($node->var, LegacyClass::PERSON)) {
            return null;
        }

        return new MethodCall($node->var, self::GETTERS[$name]);
    }
}
