<?php

declare(strict_types=1);

namespace App\ApiPlatform\Adapter;

use Doctrine\Common\Collections\Criteria;

interface CriteriaApiFilterInterface extends ApiFilterInterface
{
    public function criteria(): Criteria;
}
