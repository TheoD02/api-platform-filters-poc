<?php

declare(strict_types=1);

namespace App\Tests\ApiPlatform\Filter\Operator;

use App\ApiPlatform\Filter\Definition\FilterDefinition;
use App\ApiPlatform\Filter\Operator\BetweenOperator;
use App\ApiPlatform\Filter\Operator\ContainsOperator;
use App\ApiPlatform\Filter\Operator\EmptyOperator;
use App\ApiPlatform\Filter\Operator\EndsWithOperator;
use App\ApiPlatform\Filter\Operator\EqualsOperator;
use App\ApiPlatform\Filter\Operator\GreaterThanOperator;
use App\ApiPlatform\Filter\Operator\GreaterThanOrEqualsOperator;
use App\ApiPlatform\Filter\Operator\InOperator;
use App\ApiPlatform\Filter\Operator\IsNotNullOperator;
use App\ApiPlatform\Filter\Operator\IsNullOperator;
use App\ApiPlatform\Filter\Operator\LessThanOperator;
use App\ApiPlatform\Filter\Operator\LessThanOrEqualsOperator;
use App\ApiPlatform\Filter\Operator\MinLengthOperator;
use App\ApiPlatform\Filter\Operator\NotBetweenOperator;
use App\ApiPlatform\Filter\Operator\NotContainsOperator;
use App\ApiPlatform\Filter\Operator\NotEmptyOperator;
use App\ApiPlatform\Filter\Operator\NotEndsWithOperator;
use App\ApiPlatform\Filter\Operator\NotEqualsOperator;
use App\ApiPlatform\Filter\Operator\NotInOperator;
use App\ApiPlatform\Filter\Operator\NotStartsWithOperator;
use App\ApiPlatform\Filter\Operator\StartsWithOperator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OperatorsTest extends TestCase
{
    public $qb;

    #[\Override]
    protected function setUp(): void
    {
        $this->qb = new QueryBuilder($this->createMock(EntityManagerInterface::class));
    }

    /**
     * @dataProvider provideOperatorCases
     */
    public function testOperator(
        string $operatorClass,
        string $fieldName,
        string $queryOperatorName,
        string $description,
        string $whereClause,
        mixed $value,
        string $rootAlias
    ): void {
        // Arrange
        $operator = new $operatorClass();

        // Act
        $operator->apply(
            $this->qb,
            rootAlias: $rootAlias,
            value: $value,
            filterDefinition: FilterDefinition::create()->field($fieldName)->addOperator($operatorClass)
        );

        // Assert
        $this->assertSame($queryOperatorName, $operator->queryOperatorName());
        $this->assertSame($description, $operator->description());
        $where = $this->qb->getDQLPart('where')->getParts()[0];
        $this->assertSame($whereClause, $where);
    }

    public static function provideOperatorCases(): iterable
    {
        yield [
            'operatorClass' => BetweenOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'between',
            'description' => 'between two values',
            'whereClause' => 'a.filter BETWEEN :filter_between_1 AND :filter_between_2',
            'value' => ['value1', 'value2'],
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => ContainsOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'contains',
            'description' => 'partial string match',
            'whereClause' => 'a.filter LIKE :filter_contains',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => EmptyOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'empty',
            'description' => 'empty or null value',
            'whereClause' => "a.filter = '' OR a.filter IS NULL",
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => EndsWithOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'endswith',
            'description' => 'partial string match at the end',
            'whereClause' => 'a.filter LIKE :filter_endswith',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => EqualsOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'eq',
            'description' => 'equal to a value',
            'whereClause' => 'a.filter = :filter_eq',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => GreaterThanOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'gt',
            'description' => 'greater than a value',
            'whereClause' => 'a.filter > :filter_gt',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => GreaterThanOrEqualsOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'gte',
            'description' => 'greater than or equal to a value',
            'whereClause' => 'a.filter >= :filter_gte',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => InOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'in',
            'description' => 'in a list of values',
            'whereClause' => 'a.filter IN (:filter_in)',
            'value' => ['value1', 'value2'],
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => IsNotNullOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'isnotnull',
            'description' => 'is not null value',
            'whereClause' => 'a.filter IS NOT NULL',
            'value' => '1',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => IsNullOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'isnull',
            'description' => 'is null value',
            'whereClause' => 'a.filter IS NULL',
            'value' => '1',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => LessThanOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'lt',
            'description' => 'less than a value',
            'whereClause' => 'a.filter < :filter_lt',
            'value' => '1',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => LessThanOrEqualsOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'lte',
            'description' => 'less than or equal to a value',
            'whereClause' => 'a.filter <= :filter_lte',
            'value' => '1',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => MinLengthOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'min_length',
            'description' => 'minimum length of a string',
            'whereClause' => 'LENGTH(a.filter) >= :filter_min_length',
            'value' => 'qada',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => NotBetweenOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'notbetween',
            'description' => 'not between two values',
            'whereClause' => 'a.filter NOT BETWEEN :filter_notbetween AND :filter_notbetween',
            'value' => ['value1', 'value2'],
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => NotContainsOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'notcontains',
            'description' => 'not partial string match',
            'whereClause' => 'a.filter NOT LIKE :filter_notcontains',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => NotEmptyOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'notempty',
            'description' => 'not empty or null value',
            'whereClause' => 'a.filter IS NOT NULL AND a.filter != :filter_notempty',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => NotEndsWithOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'notendswith',
            'description' => 'not partial string match at the end',
            'whereClause' => 'a.filter NOT LIKE :filter_notendswith',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => NotEqualsOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'neq',
            'description' => 'not equal to a value',
            'whereClause' => 'a.filter != :filter_neq',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => NotInOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'nin',
            'description' => 'not in a list of values',
            'whereClause' => 'a.filter NOT IN (:filter_nin)',
            'value' => ['value1', 'value2'],
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => NotStartsWithOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'notstartswith',
            'description' => 'not partial string match at the beginning',
            'whereClause' => 'a.filter NOT LIKE :filter_notstartswith',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
        yield [
            'operatorClass' => StartsWithOperator::class,
            'fieldName' => 'filter',
            'queryOperatorName' => 'startswith',
            'description' => 'partial string match at the beginning',
            'whereClause' => 'a.filter LIKE :filter_startswith',
            'value' => 'value',
            'rootAlias' => 'a',
        ];
    }
}
