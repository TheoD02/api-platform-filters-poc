<?php

declare(strict_types=1);

namespace App\ApiPlatform\Adapter;

use App\ApiPlatform\Filter\Definition\FilterDefinitionBag;

interface ApiFilterInterface
{
    public function definition(): FilterDefinitionBag;
}
