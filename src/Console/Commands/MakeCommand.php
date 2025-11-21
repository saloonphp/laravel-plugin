<?php

declare(strict_types=1);

namespace Saloon\Laravel\Console\Commands;

use Illuminate\Support\Facades\File;
use function Laravel\Prompts\suggest;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

abstract class MakeCommand extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $stub = '';

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * Get the stub
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/../../../stubs/' . $this->resolveStubName());
    }

    protected function resolveStubName(): string
    {
        return method_exists($this, 'stub')
            ? $this->stub()
            : $this->stub;
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(mb_trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $namespace = $this->getNamespaceFromIntegrationsPath() . $this->getFormattedNamespace();

        return str_replace('{integration}', $this->getIntegration(), $rootNamespace . $namespace);
    }

    protected function getIntegration(): string
    {
        $integration = $this->argument('integration');

        if (! is_string($integration)) {
            throw new \LogicException('The {integration} argument must be a string.');
        }

        return $integration;
    }

    /**
     * Get the console command arguments.
     *
     * @return array<mixed, mixed>
     */
    protected function getArguments(): array
    {
        return [
            ['integration', InputArgument::REQUIRED, 'The related integration'],
            ...parent::getArguments(),
        ];
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string|\Closure>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'integration' => fn () => suggest(
                label: 'What is the related integration?',
                options: fn (string $value) => $this->getExistingIntegrations($value),
                required: true,
                hint: 'Start typing to search or enter a new integration name'
            ),
        ];
    }

    /**
     * Get existing integrations filtered by search value via prompt
     *
     * @return array<int, string>
     */
    protected function getExistingIntegrations(string $search = ''): array
    {
        $integrationsPath = config('saloon.integrations_path');

        if (! File::isDirectory($integrationsPath)) {
            return [];
        }

        $directories = File::directories($integrationsPath);
        $integrations = array_map(fn ($path) => basename($path), $directories);

        if (mb_strlen($search) === 0) {
            return $integrations;
        }

        return array_values(array_filter(
            $integrations,
            fn ($integration) => str_contains(mb_strtolower($integration), mb_strtolower($search))
        ));
    }

    /**
     * Returns the namespace without the default Saloon parts
     */
    protected function getFormattedNamespace(): string
    {
        return str_replace('\\Http\\Integrations', '', $this->namespace);
    }

    /**
     * Converts the integrations path to a namespace friendly string
     */
    protected function getNamespaceFromIntegrationsPath(): string
    {
        $namespace = (array)str_replace(['\\App', '\\app'], '', str_replace('/', '\\', str_replace(base_path(), '', config('saloon.integrations_path'))));

        return $namespace[0];
    }
}
