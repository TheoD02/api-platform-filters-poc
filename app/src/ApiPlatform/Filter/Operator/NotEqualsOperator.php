<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Operator;

use App\ApiPlatform\Filter\Definition\FilterDefinition;
use App\ApiPlatform\Filter\Operator\Adapter\ArrayOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\NumberOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\OrmOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\StringOperatorInterface;
use App\ApiPlatform\Filter\Trait\ParameterNameGeneratorTrait;
use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

/**
 * @see \App\Tests\ApiPlatform\Filter\Operator\NotEqualsOperatorTest
 */
class NotEqualsOperator implements OrmOperatorInterface, NumberOperatorInterface, StringOperatorInterface, ArrayOperatorInterface
{
    use ParameterNameGeneratorTrait;

    #[\Override]
    public function queryOperatorName(): string
    {
        return 'neq';
    }

    #[\Override]
    public function description(): string
    {
        return 'not equal to a value';
    }

    #[\Override]
    public function apply(QueryBuilder $qb, string $rootAlias, string|array $value, \ReflectionProperty|FilterDefinition $filterDefinition): QueryBuilder
    {
        Assert::string($value);

        $parameterName = $this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName());

        return $qb->andWhere(sprintf('%s.%s != :%s', $rootAlias, $filterDefinition->getName(), $parameterName))
            ->setParameter($parameterName, $value)
        ;
    }
}
