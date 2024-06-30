<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Operator;

use App\ApiPlatform\Filter\Definition\FilterDefinition;
use App\ApiPlatform\Filter\Operator\Adapter\ArrayOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\OrmOperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\StringOperatorInterface;
use App\ApiPlatform\Filter\Trait\ParameterNameGeneratorTrait;
use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

/**
 * @see \App\Tests\ApiPlatform\Filter\Operator\NotEmptyOperatorTest
 */
class NotEmptyOperator implements OrmOperatorInterface, StringOperatorInterface, ArrayOperatorInterface
{
    use ParameterNameGeneratorTrait;

    #[\Override]
    public function queryOperatorName(): string
    {
        return 'notempty';
    }

    #[\Override]
    public function description(): string
    {
        return 'not empty or null value';
    }

    #[\Override]
    public function apply(QueryBuilder $qb, string $rootAlias, string|array $value, \ReflectionProperty|FilterDefinition $filterDefinition): QueryBuilder
    {
        Assert::string($value);

        return $qb->andWhere(
            sprintf(
                '%s.%s IS NOT NULL AND %s.%s != :%s',
                $rootAlias,
                $filterDefinition->getName(),
                $rootAlias,
                $filterDefinition->getName(),
                $this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName())
            )
        )
            ->setParameter($this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName()), '')
        ;
    }
}
