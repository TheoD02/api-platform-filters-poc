<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use App\ApiPlatform\Filter\Applier\FilterApplierHandler;
use App\ApiPlatform\State\AbstractProvider;
use App\ApiResource\UserResource;
use App\Repository\UserRepository;
use AutoMapper\AutoMapperInterface;

/**
 * @template-extends AbstractProvider<UserResource>
 * @see \App\Tests\State\UserCollectionProviderTest
 */
final class UserCollectionProvider extends AbstractProvider
{
    public function __construct(
        private readonly UserRepository $repository,
        FilterApplierHandler $filterApplierHandler,
        AutoMapperInterface $autoMapper,
        Pagination $pagination,
    ) {
        parent::__construct($filterApplierHandler, $autoMapper, $pagination);
    }

    /**
     * @return array<UserResource>
     */
    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $qb = $this->repository->createQueryBuilder('u');

        return $this->mapItems($qb, $operation, $context);
    }
}
