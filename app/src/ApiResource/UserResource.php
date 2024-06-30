<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\UserCollectionFilter;
use App\State\UserCollectionProvider;

#[ApiResource(operations: [new GetCollection(uriTemplate: '/users', filters: [UserCollectionFilter::class], provider: UserCollectionProvider::class)])]
class UserResource
{
    #[ApiProperty(identifier: true)]
    public int $id;

    public string $name;
}
