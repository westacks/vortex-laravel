<?php

namespace WeStacks\Laravel\Vortex\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'vortex:middleware')]
class CreateMiddleware extends GeneratorCommand
{
    protected $name = 'vortex:middleware';
    protected $description = 'Create a new Vortex middleware';
    protected $type = 'Middleware';

    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/middleware.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Http\Middleware';
    }

    protected function getArguments(): array
    {
        return [[
            'name',
            InputOption::VALUE_REQUIRED,
            'Name of the Middleware that should be created',
            'HandleVortexRequests'
        ]];
    }

    protected function getOptions(): array
    {
        return [[
            'force',
            null,
            InputOption::VALUE_NONE,
            'Create the class even if the Middleware already exists'
        ]];
    }
}
