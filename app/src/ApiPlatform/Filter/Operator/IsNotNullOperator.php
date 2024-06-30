<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Operator;

use App\ApiPlatform\Filter\Definition\FilterDefinition;
use App\ApiPlatform\Filter\Operator\Adapter\ArrayOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\NumberOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\OrmOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\StringOperatorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @see \App\Tests\ApiPlatform\Filter\Operator\IsNotNullOperatorTest
 */
class IsNotNullOperator implements OrmOperatorInterface, NumberOperatorInterface, StringOperatorInterface, ArrayOperatorInterface
{
    #[\Override]
    public function queryOperatorName(): string
    {
        return 'isnotnull';
    }

    #[\Override]
    public function description(): string
    {
        return 'is not null value';
    }

    #[\Override]
    public function apply(QueryBuilder $qb, string $rootAlias, string|array $value, \ReflectionProperty|FilterDefinition $filterDefinition): QueryBuilder
    {
        return $qb->andWhere(sprintf('%s.%s IS NOT NULL', $rootAlias, $filterDefinition->getName()));
    }
}
