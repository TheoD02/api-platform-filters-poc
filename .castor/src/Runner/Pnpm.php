<?php

declare(strict_types=1);

namespace TheoD\ApiPlatformFiltersPOC\Runner;

use Castor\Context;
use TheoD\ApiPlatformFiltersPOC\ContainerDefinitionBag;
use TheoD\ApiPlatformFiltersPOC\Docker\ContainerDefinition;

use function Castor\io;
use function TheoD\ApiPlatformFiltersPOC\app_context;

class Pnpm extends Runner
{
    public function __construct(
        ?Context $context = null,
        ?ContainerDefinition $containerDefinition = null,
        bool $preventRunningUsingDocker = false,
    ) {
        if (
            ! is_file(app_context()->workingDirectory . '/package.json')
            && ! is_file(app_context()->workingDirectory . '/yarn.lock')
        ) {
            io()->warning('No package.json or yarn.lock file found in the working directory');
        }

        parent::__construct(
            context: $context,
            containerDefinition: $containerDefinition ?? ContainerDefinitionBag::node(),
            preventRunningUsingDocker: $preventRunningUsingDocker
        );
    }

    protected function getBaseCommand(): ?string
    {
        return 'pnpm';
    }

    public function install(string|int ...$args): static
    {
        return $this->add('install', ...$args);
    }
}

function pnpm(?Context $context = null): Pnpm
{
    return new Pnpm($context);
}
