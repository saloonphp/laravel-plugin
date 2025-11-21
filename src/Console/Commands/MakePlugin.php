<?php

declare(strict_types=1);

namespace Saloon\Laravel\Console\Commands;

class MakePlugin extends MakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'saloon:plugin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Saloon plugin';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Saloon Plugin';

    /**
     * The namespace to place the file
     *
     * @var string
     */
    protected $namespace = '\Http\Integrations\{integration}\Plugins';

    /**
     * The default stub
     *
     * @var string
     */
    protected $stub = 'saloon.plugin.stub';

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string|\Closure>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            ...parent::promptForMissingArgumentsUsing(),
            'name' => 'What should the Saloon plugin be named?',
        ];
    }
}
