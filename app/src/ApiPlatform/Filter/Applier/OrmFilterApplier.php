<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Applier;

use App\ApiPlatform\Filter\Operator\Adapter\OperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\OrmOperatorInterface;
use Doctrine\ORM\QueryBuilder;

class OrmFilterApplier
{
    /**
     * @param array<string, array{operator: OperatorInterface&OrmOperatorInterface, queryFieldName: string, queryFieldValue: string}> $parameters
     */
    public function apply(QueryBuilder $qb, \ReflectionClass $reflectionClass, array $parameters = []): void
    {
        $rootAlias = $qb->getRootAliases()[0];

        foreach ($parameters as $parameter) {
            $fieldName = $parameter['fieldName'];
            /** @var OperatorInterface&OrmOperatorInterface $operator */
            $operator = $parameter['operator'];
            $queryFieldValue = $parameter['queryFieldValue'];
            $definition = $parameter['definition'];

            $reflectionProperty = $reflectionClass->hasProperty($fieldName) ? $reflectionClass->getProperty($fieldName) : $definition;
            $operator->apply($qb, $rootAlias, $queryFieldValue, $reflectionProperty);
        }
    }
}
