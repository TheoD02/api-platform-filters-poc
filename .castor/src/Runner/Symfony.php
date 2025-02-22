<?php

declare(strict_types=1);

namespace TheoD\ApiPlatformFiltersPOC\Runner;

use Castor\Context;
use TheoD\ApiPlatformFiltersPOC\ContainerDefinitionBag;
use TheoD\ApiPlatformFiltersPOC\Docker\ContainerDefinition;

class Symfony extends Runner
{
    public function __construct(
        ?Context $context = null,
        ?ContainerDefinition $containerDefinition = null,
        bool $preventRunningUsingDocker = false,
    ) {
        parent::__construct(
            context: $context,
            containerDefinition: $containerDefinition ?? ContainerDefinitionBag::php(),
            preventRunningUsingDocker: $preventRunningUsingDocker
        );
    }

    protected function getBaseCommand(): ?string
    {
        return 'php bin/console';
    }

    public function console(string|int ...$args): static
    {
        return $this->add(...$args);
    }
}

function symfony(?Context $context = null): Symfony
{
    return new Symfony($context);
}
