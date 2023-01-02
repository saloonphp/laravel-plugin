<?php

declare(strict_types=1);

namespace Saloon\Laravel\Console\Commands;

class MakeOAuthConnector extends MakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'saloon:oauth-connector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Saloon OAuth2 connector class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Saloon OAuth2 Connector';

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
    protected $stub = 'saloon.oauth-connector.stub';
}
