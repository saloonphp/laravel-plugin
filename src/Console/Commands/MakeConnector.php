<?php

namespace Sammyjo20\SaloonLaravel\Console\Commands;

class MakeConnector extends MakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'saloon:connector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Saloon connector class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Saloon Connector';

    /**
     * The namespace to place the file
     *
     * @var string
     */
    protected $namespace = '\Http\Integrations\{integration}';

    /**
     * The default stub
     *
     * @var string
     */
    protected $stub = 'saloon.connector.stub';
}
