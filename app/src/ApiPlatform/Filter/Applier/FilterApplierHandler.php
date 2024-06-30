<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Applier;

use App\ApiPlatform\Adapter\QueryBuilderApiFilterInterface;
use App\ApiPlatform\Filter\Definition\FilterDefinitionBag;
use App\ApiPlatform\Filter\Operator\Adapter\OperatorInterface;
use App\ApiPlatform\Filter\Operator\Adapter\OrmOperatorInterface;
use App\ApiPlatform\RequestToDTOTransformer;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class FilterApplierHandler
{
    public function __construct(
        private RequestStack $requestStack,
        private OrmFilterApplier $ormFilterApplier,
        private RequestToDTOTransformer $requestToDTOTransformer,
    ) {
    }

    /**
     * @return array<string, array{operator: OperatorInterface, queryFieldName: string, queryFieldValue: string}>
     */
    private function parseQueryParameters(\ReflectionClass $reflectionClass, array $for = ['orm']): array
    {
        $queryParameters = $this->requestStack->getCurrentRequest()->query->all();
        /** @var array<string, array{operator: OperatorInterface, queryFieldName: string, queryFieldValue: string}> $parameters */
        $parameters = [];

        /** @var FilterDefinitionBag $filterDefinitionBag */
        $filterDefinitionBag = $reflectionClass->newInstance()->definition();

        foreach ($filterDefinitionBag->getFilterDefinitions() as $filterDefinition) {
            foreach ($filterDefinition->getOperators() as $operator) {
                $queryFieldName = "{$filterDefinition->getField()}[{$operator->queryOperatorName()}]";
                $queryFieldValue = $queryParameters[$filterDefinition->getField()][$operator->queryOperatorName()] ?? null;

                if ($queryFieldValue === null) {
                    continue;
                }

                if ($operator instanceof OrmOperatorInterface && ! \in_array('orm', $for, true)) {
                    continue;
                }

                $parameters["{$filterDefinition->getField()}_{$operator->queryOperatorName()}"] = [
                    'definition' => $filterDefinition,
                    'fieldName' => $filterDefinition->getField(),
                    'operator' => $operator,
                    'queryFieldName' => $queryFieldName,
                    'queryFieldValue' => $queryFieldValue,
                ];
            }
        }

        return $parameters;
    }

    /**
     * @param class-string $filterClass
     */
    public function applyOrm(QueryBuilder $qb, string $filterClass): void
    {
        $reflectionClass = new \ReflectionClass($filterClass);
        $parameters = $this->parseQueryParameters($reflectionClass, for: ['orm']);

        if (is_subclass_of($filterClass, QueryBuilderApiFilterInterface::class)) {
            $request = $this->requestStack->getCurrentRequest();
            $filterClass = $this->requestToDTOTransformer->transformQueryString($request, $filterClass);

            if ($filterClass instanceof QueryBuilderApiFilterInterface) {
                $filterClass->applyToQueryBuilder($qb);
            }
        }

        $this->ormFilterApplier->apply($qb, $reflectionClass, $parameters);
    }
}
