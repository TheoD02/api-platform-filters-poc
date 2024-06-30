<?php

declare(strict_types=1);

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\ApiPlatform\Adapter\ApiFilterInterface;
use App\ApiPlatform\Filter\Applier\FilterApplierHandler;
use AutoMapper\AutoMapperInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;

/**
 * @template T of object
 *
 * @implements ProviderInterface<T>
 */
abstract class AbstractProvider implements ProviderInterface
{
    public function __construct(
        private readonly FilterApplierHandler $filterApplierHandler,
        private readonly AutoMapperInterface $autoMapper,
        private readonly Pagination $pagination,
    ) {
    }

    public function applyFilters(QueryBuilder $qb, Operation $operation): void
    {
        $filters = $operation->getFilters();

        if ($filters === null) {
            return;
        }

        foreach ($filters as $filter) {
            if (is_subclass_of($filter, ApiFilterInterface::class)) {
                $this->filterApplierHandler->applyOrm($qb, $filter);
            }
        }
    }

    /**
     * @template A of object
     *
     * @param array<mixed>         $context
     * @param class-string<A>|null $target  (default to operation output or resource class, use this only for overriding the default behavior)
     *
     * @return ($target is null ? array<T> : array<A>)
     */
    public function mapItems(object $collection, Operation $operation, array $context, ?string $target = null): array
    {
        /**
         * @var int $currentPage
         * @var int $itemsPerPage
         */
        [$currentPage,, $itemsPerPage] = $this->pagination->getPagination($operation, $context);
        if ($collection instanceof QueryBuilder) {
            $this->applyFilters($collection, $operation);
            $collection->setFirstResult(($currentPage - 1) * $itemsPerPage);
            $collection->setMaxResults($itemsPerPage);

            $items = $collection->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        } else {
            throw new \RuntimeException('Unsupported collection type');
        }

        if ($target === null) {
            /** @var class-string $target */
            $target = $operation->getOutput() ?? $operation->getClass();
        }

        if (is_iterable($items)) {
            /** @var ($target is null ? array<T> : array<A>) $returnedItems */
            $returnedItems = [];
            /** @var array<mixed> $item */
            foreach ($items as $item) {
                /** @var ($target is null ? T : A) $mappedItem */
                $mappedItem = $this->autoMapper->map($item, $target);
                $returnedItems[] = $mappedItem;
            }

            return $returnedItems;
        }

        throw new \RuntimeException('Unsupported collection type');
    }
}
