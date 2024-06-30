<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Definition;

use App\ApiPlatform\Filter\Operator\Adapter\OperatorInterface;
use App\ApiPlatform\Filter\Operator\ContainsOperator;
use App\ApiPlatform\Filter\Operator\EndsWithOperator;
use App\ApiPlatform\Filter\Operator\EqualsOperator;
use App\ApiPlatform\Filter\Operator\GreaterThanOperator;
use App\ApiPlatform\Filter\Operator\GreaterThanOrEqualsOperator;
use App\ApiPlatform\Filter\Operator\InOperator;
use App\ApiPlatform\Filter\Operator\IsNotNullOperator;
use App\ApiPlatform\Filter\Operator\IsNullOperator;
use App\ApiPlatform\Filter\Operator\LessThanOperator;
use App\ApiPlatform\Filter\Operator\LessThanOrEqualsOperator;
use App\ApiPlatform\Filter\Operator\NotEqualsOperator;
use App\ApiPlatform\Filter\Operator\NotInOperator;
use App\ApiPlatform\Filter\Operator\StartsWithOperator;

class FilterDefinition
{
    private string $field = '';

    /**
     * @var array<OperatorInterface>
     */
    private array $operators = [];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function field(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function operators(OperatorInterface ...$operators): self
    {
        $this->operators = $operators;

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getName(): string
    {
        return $this->field;
    }

    private function instantiateOperator(string $operatorFqcn): OperatorInterface
    {
        $operator = new $operatorFqcn();
        if (! $operator instanceof OperatorInterface) {
            throw new \InvalidArgumentException(sprintf('Operator must implement %s', OperatorInterface::class));
        }

        return $operator;
    }

    public function getOperators(): array
    {
        return $this->operators;
    }

    public function getOperator(string $name): OperatorInterface
    {
        return $this->operators[$name];
    }

    public function hasOperator(string $name): bool
    {
        return isset($this->operators[$name]);
    }

    public function addOperator(string $operatorFqcn): self
    {
        $operator = $this->instantiateOperator($operatorFqcn);

        $this->operators[$operator->queryOperatorName()] = $operator;

        return $this;
    }

    public function addOperators(string ...$operatorFqcns): self
    {
        foreach ($operatorFqcns as $operatorFqcn) {
            $this->addOperator($operatorFqcn);
        }

        return $this;
    }

    public function removeOperator(string $name): self
    {
        unset($this->operators[$name]);

        return $this;
    }

    public function addStringOperators(array $exclude = []): self
    {
        $this->addOperator(ContainsOperator::class);
        $this->addOperator(EndsWithOperator::class);
        $this->addOperator(StartsWithOperator::class);
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(InOperator::class);
        $this->addOperator(NotInOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }

    public function addNumericOperators(array $exclude = []): self
    {
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(GreaterThanOperator::class);
        $this->addOperator(GreaterThanOrEqualsOperator::class);
        $this->addOperator(LessThanOperator::class);
        $this->addOperator(LessThanOrEqualsOperator::class);
        $this->addOperator(InOperator::class);
        $this->addOperator(NotInOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }

    public function addBooleanOperators(array $exclude = []): self
    {
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }

    public function addDateTimeOperators(array $exclude = []): self
    {
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(GreaterThanOperator::class);
        $this->addOperator(GreaterThanOrEqualsOperator::class);
        $this->addOperator(LessThanOperator::class);
        $this->addOperator(LessThanOrEqualsOperator::class);
        $this->addOperator(InOperator::class);
        $this->addOperator(NotInOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }

    public function addArrayOperators(array $exclude = []): self
    {
        $this->addOperator(ContainsOperator::class);
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(InOperator::class);
        $this->addOperator(NotInOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }
}
