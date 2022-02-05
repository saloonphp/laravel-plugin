<?php

namespace Sammyjo20\SaloonLaravel\Console\Commands;

class MakeRequest extends MakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'saloon:request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Saloon request class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Saloon Request';

    /**
     * The namespace to place the file
     *
     * @var string
     */
    protected $namespace = '\Http\Integrations\{integration}\Requests';

    /**
     * The default stub
     *
     * @var string
     */
    protected $stub = 'saloon.request.stub';
}
