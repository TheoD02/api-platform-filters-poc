<?php

declare(strict_types=1);

namespace App\ApiPlatform;

use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\HeaderParameterInterface;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Parameter;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\OpenApi\Model\Parameter as OpenApiParameter;
use ApiPlatform\Serializer\Filter\FilterInterface as SerializerFilterInterface;
use App\ApiPlatform\Adapter\ApiFilterInterface;
use App\ApiPlatform\Attribute\ApiParameter;
use App\ApiPlatform\Attribute\AsApiFilter;
use App\ApiPlatform\Filter\Definition\FilterDefinitionBag;
use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\DivisibleBy;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FilterHandlerResourceMetadataFactory implements ResourceMetadataCollectionFactoryInterface
{
    private static array $alreadyProcessed = [];

    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $decorated,
        private readonly ?ContainerInterface $filterLocator = null,
    ) {
    }

    #[\Override]
    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $resourceMetadataCollection = $this->decorated->create($resourceClass);

        foreach ($resourceMetadataCollection as $resource) {
            $operations = $resource->getOperations();

            if (! $operations instanceof Operations) {
                continue;
            }

            $internalPriority = -1;
            foreach ($operations as $operationName => $operation) {
                if (\array_key_exists($operationName, self::$alreadyProcessed)) {
                    continue;
                }

                self::$alreadyProcessed[$operationName] = true;
                $filters = $operation->getFilters() ?? [];

                if ($filters === []) {
                    continue;
                }

                /** @var array<Parameter> $parameters */
                $parameters = [...$operation->getParameters() ?? []];
                foreach ($filters as $filter) {
                    if (class_exists($filter) && is_subclass_of($filter, ApiFilterInterface::class)) {
                        $reflectionClass = new \ReflectionClass($filter);
                        $attribute = $reflectionClass->getAttributes(AsApiFilter::class)[0] ?? null;

                        if ($attribute === null) {
                            continue;
                        }

                        /** @var FilterDefinitionBag $definition */
                        $definition = $reflectionClass->newInstance()->definition();
                        foreach ($definition->getFilterDefinitions() as $filterDefinition) {
                            $filterName = $filterDefinition->getField();
                            $filterOperators = $filterDefinition->getOperators();

                            foreach ($filterOperators as $filterOperator) {
                                $parameters[$filterName . $filterOperator->queryOperatorName()] = new QueryParameter(
                                    key: "{$filterName}[{$filterOperator->queryOperatorName()}]",
                                    schema: [
                                        'type' => 'string',
                                    ],
                                    description: "Filter {$filterName} by {$filterOperator->description()}",
                                    required: false,
                                );
                            }
                        }

                        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                            $propertyName = $reflectionProperty->getName();

                            $apiParameter = $reflectionProperty->getAttributes(ApiParameter::class)[0] ?? null;
                            $apiParameter = $apiParameter?->newInstance();

                            if ($apiParameter === null) {
                                throw new \LogicException(sprintf(
                                    'The property "%s" in the filter "%s" must have the #[ApiParameter] attribute.',
                                    $propertyName,
                                    $filter
                                ));
                            }

                            if ($apiParameter->getKey() === null) {
                                $apiParameter = $apiParameter->withKey($reflectionProperty->getName());
                            }

                            $parameters[$apiParameter->getKey()] = $apiParameter;
                        }
                    }
                }

                foreach ($parameters as $key => $parameter) {
                    $parameter = $this->setDefaults($key, $parameter, $resourceClass);
                    $priority = $parameter->getPriority() ?? $internalPriority--;
                    $parameters[$key] = $parameter->withPriority($priority);
                }

                $operations->add($operationName, $operation->withParameters(new Parameters($parameters)));
            }
        }

        return $resourceMetadataCollection;
    }

    private function setDefaults(string $key, Parameter $parameter, string $resourceClass): Parameter
    {
        if ($parameter->getKey() === null) {
            $parameter = $parameter->withKey($key);
        }

        $filter = $parameter->getFilter();
        if (\is_string($filter) && $this->filterLocator->has($filter)) {
            $filter = $this->filterLocator->get($filter);
        }

        if ($filter instanceof SerializerFilterInterface && $parameter->getProvider() === null) {
            $parameter = $parameter->withProvider('api_platform.serializer.filter_parameter_provider');
        }

        // Read filter description to populate the Parameter
        $description = $filter instanceof FilterInterface ? $filter->getDescription($resourceClass) : [];
        if (($schema = $description[$key]['schema'] ?? null) && $parameter->getSchema() === null) {
            $parameter = $parameter->withSchema($schema);
        }

        if ($parameter->getProperty() === null && ($property = $description[$key]['property'] ?? null)) {
            $parameter = $parameter->withProperty($property);
        }

        if ($parameter->getRequired() === null && ($required = $description[$key]['required'] ?? null)) {
            $parameter = $parameter->withRequired($required);
        }

        if (! $parameter->getOpenApi() instanceof OpenApiParameter && $openApi = $description[$key]['openapi'] ?? null) {
            if ($openApi instanceof OpenApiParameter) {
                $parameter = $parameter->withOpenApi($openApi);
            } elseif (\is_array($openApi)) {
                /** @phpstan-ignore-next-line */
                $schema = $schema ?? $openapi['schema'] ?? [];
                $parameter = $parameter->withOpenApi(new OpenApiParameter(
                    $key,
                    $parameter instanceof HeaderParameterInterface ? 'header' : 'query',
                    $description[$key]['description'] ?? '',
                    $description[$key]['required'] ?? $openApi['required'] ?? false,
                    $openApi['deprecated'] ?? false,
                    $openApi['allowEmptyValue'] ?? true,
                    $schema,
                    $openApi['style'] ?? null,
                    $openApi['explode'] ?? ('array' === ($schema['type'] ?? null)),
                    $openApi['allowReserved'] ?? false,
                    $openApi['example'] ?? null,
                    isset(
                        $openApi['examples']
                    ) ? new \ArrayObject($openApi['examples']) : null
                ));
            }
        }

        $schema = $parameter->getSchema() ?? $parameter->getOpenApi()?->getSchema();

        // Only add validation if the Symfony Validator is installed
        if (interface_exists(ValidatorInterface::class) && ! $parameter->getConstraints()) {
            return $this->addSchemaValidation(
                $parameter,
                $schema,
                $parameter->getRequired() ?? $description['required'] ?? false,
                $parameter->getOpenApi()
            );
        }

        return $parameter;
    }

    private function addSchemaValidation(
        Parameter $parameter,
        ?array $schema = null,
        bool $required = false,
        OpenApiParameter|bool|null $openApi = null
    ): Parameter {
        $assertions = [];

        if ($required) {
            $assertions[] = new NotNull(message: sprintf('The parameter "%s" is required.', $parameter->getKey()));
        }

        if (isset($schema['exclusiveMinimum'])) {
            $assertions[] = new GreaterThan(value: $schema['exclusiveMinimum']);
        }

        if (isset($schema['exclusiveMaximum'])) {
            $assertions[] = new LessThan(value: $schema['exclusiveMaximum']);
        }

        if (isset($schema['minimum'])) {
            $assertions[] = new GreaterThanOrEqual(value: $schema['minimum']);
        }

        if (isset($schema['maximum'])) {
            $assertions[] = new LessThanOrEqual(value: $schema['maximum']);
        }

        if (isset($schema['pattern'])) {
            $assertions[] = new Regex($schema['pattern']);
        }

        if (isset($schema['maxLength']) || isset($schema['minLength'])) {
            $assertions[] = new Length(min: $schema['minLength'] ?? null, max: $schema['maxLength'] ?? null);
        }

        if (isset($schema['minItems']) || isset($schema['maxItems'])) {
            $assertions[] = new Count(min: $schema['minItems'] ?? null, max: $schema['maxItems'] ?? null);
        }

        if (isset($schema['multipleOf'])) {
            $assertions[] = new DivisibleBy(value: $schema['multipleOf']);
        }

        if ($schema['uniqueItems'] ?? false) {
            $assertions[] = new Unique();
        }

        if (isset($schema['enum'])) {
            $assertions[] = new Choice(choices: $schema['enum']);
        }

        if ($openApi?->getAllowEmptyValue() === false) {
            $assertions[] = new NotBlank(allowNull: ! $required);
        }

        if ($assertions === []) {
            return $parameter;
        }

        if (\count($assertions) === 1) {
            return $parameter->withConstraints($assertions[0]);
        }

        return $parameter->withConstraints($assertions);
    }
}
