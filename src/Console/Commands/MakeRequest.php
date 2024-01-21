<?php

declare(strict_types=1);

namespace Saloon\Laravel\Console\Commands;

use Saloon\Enums\Method;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use function Laravel\Prompts\select;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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

    /**
     * Get the options for making a request
     *
     * @return array<int, array<mixed>>
     */
    protected function getOptions(): array
    {
        return [
            ['method', 'm', InputOption::VALUE_REQUIRED, 'the method of the request'],
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        if ($this->didReceiveOptions($input)) {
            return;
        }

        $methodType = select(
            'What method should the saloon request send?',
            Arr::pluck(Method::cases(), 'name')
        );

        $input->setOption('method', $methodType);
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     *
     * @throws FileNotFoundException
     */
    protected function buildClass($name): MakeRequest|string
    {
        $method = $this->option('method') ?? 'GET';

        if (! is_string($method)) {
            throw new InvalidArgumentException('The method option must be a string.');
        }

        $stub = $this->files->get($this->getStub());
        $stub = $this->replaceMethod($stub, $method);

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the method for the stub
     */
    protected function replaceMethod(string $stub, string $name): string
    {
        return str_replace('{{ method }}', $name, $stub);
    }
}
