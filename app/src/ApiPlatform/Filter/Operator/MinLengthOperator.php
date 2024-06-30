<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Operator;

use App\ApiPlatform\Filter\Definition\FilterDefinition;
use App\ApiPlatform\Filter\Operator\Adapter\OrmOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\StringOperatorInterface;
use App\ApiPlatform\Filter\Trait\ParameterNameGeneratorTrait;
use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

/**
 * @see \App\Tests\ApiPlatform\Filter\Operator\MinLengthOperatorTest
 */
class MinLengthOperator implements OrmOperatorInterface, StringOperatorInterface
{
    use ParameterNameGeneratorTrait;

    #[\Override]
    public function queryOperatorName(): string
    {
        return 'min_length';
    }

    #[\Override]
    public function description(): string
    {
        return 'minimum length of a string';
    }

    #[\Override]
    public function apply(QueryBuilder $qb, string $rootAlias, string|array $value, \ReflectionProperty|FilterDefinition $filterDefinition): QueryBuilder
    {
        Assert::string($value);

        $parameterName = $this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName());

        return $qb->andWhere(sprintf('LENGTH(%s.%s) >= :%s', $rootAlias, $filterDefinition->getName(), $parameterName))
            ->setParameter($parameterName, (int) $value)
        ;
    }
}
