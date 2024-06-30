<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Operator\Adapter;

use App\ApiPlatform\Filter\Definition\FilterDefinition;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface OrmOperatorInterface
{
    /**
     * @param array<mixed>|string $value
     */
    public function apply(QueryBuilder $qb, string $rootAlias, string|array $value, \ReflectionProperty|FilterDefinition $filterDefinition): QueryBuilder;
}
