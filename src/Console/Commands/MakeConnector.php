<?php

declare(strict_types=1);

namespace Saloon\Laravel\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

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

    protected function resolveStubName(): string
    {
        return $this->option('oauth')
            ? 'saloon.oauth-connector.stub'
            : 'saloon.connector.stub';
    }

    protected function getOptions()
    {
        return [
            ['oauth', null, InputOption::VALUE_NONE, 'Whether the connector should include the OAuth boilerplate'],
        ];
    }
}
