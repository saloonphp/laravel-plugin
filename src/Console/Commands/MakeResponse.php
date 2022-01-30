<?php

namespace Sammyjo20\SaloonLaravel\Console\Commands;

class MakeResponse extends MakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'saloon:response';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom Saloon response class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Saloon Response';

    /**
     * The namespace to place the file
     *
     * @var string
     */
    protected $namespace = '\Http\Integrations\{integration}\Responses';

    /**
     * The default stub
     *
     * @var string
     */
    protected $stub = 'saloon.response.stub';
}
