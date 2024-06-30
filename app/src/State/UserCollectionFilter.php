<?php

declare(strict_types=1);

namespace App\State;

use App\ApiPlatform\Adapter\ApiFilterInterface;
use App\ApiPlatform\Adapter\QueryBuilderApiFilterInterface;
use App\ApiPlatform\Attribute\ApiParameter;
use App\ApiPlatform\Attribute\AsApiFilter;
use App\ApiPlatform\Filter\Definition\FilterDefinition;
use App\ApiPlatform\Filter\Definition\FilterDefinitionBag;
use App\ApiPlatform\Filter\Operator\ContainsOperator;
use Doctrine\ORM\QueryBuilder;

#[AsApiFilter]
class UserCollectionFilter implements ApiFilterInterface, QueryBuilderApiFilterInterface
{
    #[ApiParameter(description: 'Filter by something.')]
    private ?string $customFilter = null;

    #[\Override]
    public function definition(): FilterDefinitionBag
    {
        return new FilterDefinitionBag(FilterDefinition::create()->field('name')->addOperator(ContainsOperator::class));
    }

    #[\Override]
    public function applyToQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        // Need some relations to filter by ?
        // $qb->leftJoin('u.relations', 'r');

        if ($this->customFilter !== null && $this->customFilter !== '' && $this->customFilter !== '0') {
            $qb
                ->andWhere('u.id > :customFilter')
                ->setParameter('customFilter', $this->customFilter)
            ;
        }

        return $qb;
    }
}
