<?php

namespace Sammyjo20\SaloonLaravel\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

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
    protected $namespace = '\Http\Integration\{integration}\Requests';

    /**
     * The default stub
     *
     * @var string
     */
    protected $stub = 'saloon.request.stub';

    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());
        $response = new MakeResponse($this->files);

    }

    protected function getOptions()
    {
        return [
            ...parent::getOptions(),
            ['response', 'r',InputOption::VALUE_OPTIONAL, 'Specify an optional response class which will be generated and associated with this request'],
        ];
    }
}
