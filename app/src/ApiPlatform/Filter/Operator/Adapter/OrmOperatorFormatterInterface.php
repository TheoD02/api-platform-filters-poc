<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Operator\Adapter;

interface OrmOperatorFormatterInterface
{
    public function formatSQL(string $field, string $operator, string $parameterName): string;
}
