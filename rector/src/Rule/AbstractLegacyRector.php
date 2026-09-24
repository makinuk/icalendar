<?php

declare(strict_types=1);

namespace Makinuk\ICalendar\Rector\Rule;

use Makinuk\ICalendar\Enum\Method;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;

/**
 * Helpers shared by the 2.x to 3.0 upgrade rules.
 */
abstract class AbstractLegacyRector extends AbstractRector
{
    protected function isInstanceOf(Expr $expr, string $class): bool
    {
        return $this->isObjectType($expr, new ObjectType($class));
    }

    protected function isNewOf(Expr $expr, string $class): bool
    {
        return $expr instanceof New_ && $this->isName($expr->class, $class);
    }

    /**
     * Returns the positional argument values of a call, or null when it uses named, unpacked
     * or first-class-callable arguments that the rules do not handle.
     *
     * @param array<\PhpParser\Node> $args
     *
     * @return list<Expr>|null
     */
    protected function positionalValues(array $args): ?array
    {
        $values = [];
        foreach ($args as $arg) {
            if (!$arg instanceof Arg || $arg->name !== null || $arg->unpack || $arg->byRef) {
                return null;
            }
            $values[] = $arg->value;
        }

        return $values;
    }

    protected function isNullLiteral(Expr $expr): bool
    {
        return $expr instanceof ConstFetch && $this->isName($expr, 'null');
    }

    protected function isFalseLiteral(Expr $expr): bool
    {
        return $expr instanceof ConstFetch && $this->isName($expr, 'false');
    }

    protected function isZeroLiteral(Expr $expr): bool
    {
        return $expr instanceof Int_ && $expr->value === 0;
    }

    /**
     * Turns a 2.x method string into the Method enum: 'REQUEST' becomes Method::Request,
     * any other expression becomes Method::from($expr).
     */
    protected function methodEnum(Expr $expr): Expr
    {
        $enum = new FullyQualified(Method::class);

        if ($expr instanceof String_) {
            $method = Method::tryFrom(strtoupper($expr->value));
            if ($method !== null) {
                return new ClassConstFetch($enum, $method->name);
            }
        }

        return new StaticCall($enum, 'from', [new Arg($expr)]);
    }

    protected function isMethodEnum(Expr $expr): bool
    {
        return ($expr instanceof ClassConstFetch || $expr instanceof StaticCall)
            && $this->isName($expr->class, Method::class);
    }

    protected function namedArg(string $name, Expr $value): Arg
    {
        return new Arg($value, false, false, [], new Identifier($name));
    }
}
