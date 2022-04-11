<?php

namespace Sammyjo20\SaloonLaravel\Console\Commands;

class MakeAuthenticator extends MakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'saloon:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Saloon authenticator';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Saloon Authenticator';

    /**
     * The namespace to place the file
     *
     * @var string
     */
    protected $namespace = '\Http\Integrations\{integration}\Auth';

    /**
     * The default stub
     *
     * @var string
     */
    protected $stub = 'saloon.authenticator.stub';
}
