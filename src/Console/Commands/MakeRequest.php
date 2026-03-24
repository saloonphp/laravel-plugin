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
            ['route', 'r', InputOption::VALUE_REQUIRED, 'the route url of the request'],
            ['params', 'p', InputOption::VALUE_REQUIRED, 'the params of the request'],
        ];
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string|\Closure>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            ...parent::promptForMissingArgumentsUsing(),
            'name' => 'What should the Saloon request be named?',
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
        $stub = $this->replaceRoute($stub, $this->option('route','/example'));
        $stub = $this->replaceParams($stub, $this->option('params','[]'));

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the method for the stub
     */
    protected function replaceMethod(string $stub, string $name): string
    {
        return str_replace('{{ method }}', $name, $stub);
    }

    protected function replaceRoute(string $stub, string $route): string
    {
        $paramList = json_decode($this->option('params','[]'));
        if (count($paramList) > 0) {

            $paramJoin = collect($paramList)->map(function($v){return "'{" . $v . "}'";})->join(',');
            $paramVars = collect($paramList)->map(function($v){return '$this->'. $v;})->join(',');
            $code = " return str('$route')->replace([$paramJoin],[$paramVars]);";

            return str_replace('{{ return_route }}', $code, $stub);
        }


        return str_replace('{{ return_route }}', "return '$route';", $stub);
    }

    protected function replaceParams(string $stub, string $jsonParamList): string
    {
        $list = json_decode($jsonParamList, true);
        $code = '';
        foreach ($list as $param ) {
        $code .= "\n           public string \$" . $param . ", ";

        }
        return str_replace('{{ params }}', $code, $stub);
    }
}
