<?php

namespace Sammyjo20\SaloonLaravel\Console\Commands;

class MakeKeychain extends MakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'saloon:keychain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Saloon keychain';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Saloon Keychain';

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
    protected $stub = 'saloon.keychain.stub';
}
