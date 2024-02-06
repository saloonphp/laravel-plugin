<?php

declare(strict_types=1);

namespace Saloon\Laravel\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class ListCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'saloon:list';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List all Saloon Authenticators, Connectors, Requests, Plugins and Responses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();

        $this->components->twoColumnDetail(
            '<fg=green;options=bold>General</>',
            '<fg=white>Integrations: ' . count($this->getIntegrations()) . '</>'
        );

        $this->newLine();

        foreach ($this->getIntegrations() as $integration) {
            $this->getIntegrationOutput($integration);

            foreach ($this->getIntegrationAuthenticators($integration) as $integrationAuthenticator) {
                $this->getIntegrationAuthenticatorOutput($integrationAuthenticator);
            }

            foreach ($this->getIntegrationConnectors($integration) as $integrationConnector) {
                $this->getIntegrationConnectorOutput($integrationConnector);
            }

            foreach ($this->getIntegrationRequests($integration) as $integrationRequest) {
                $this->getIntegrationRequestOutput($integrationRequest);
            }

            foreach ($this->getIntegrationPlugins($integration) as $integrationPlugin) {
                $this->getIntegrationPluginOutput($integrationPlugin);
            }

            foreach ($this->getIntegrationResponses($integration) as $integrationResponse) {
                $this->getIntegrationResponseOutput($integrationResponse);
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function getIntegrations(): array
    {
        $finder = new Finder();
        $integrations = [];

        foreach ($finder->directories()->in(config('saloon.integrations_path'))->depth(0) as $integration) {
            $integrations[] = $integration->getRealPath();
        }

        return $integrations;
    }

    /**
     * @return array<int, string>
     */
    protected function getIntegrationConnectors(string $integration): array
    {
        $finder = new Finder();
        $connectors = [];

        foreach ($finder->files()->in($integration)->depth(0) as $connector) {
            $connectors[] = $connector->getRealPath();
        }

        return $connectors;
    }

    /**
     * @return array<int, string>
     */
    protected function getIntegrationRequests(string $integration): array
    {
        $finder = new Finder();
        $requests = [];

        if (is_dir($integration . '/Requests')) {
            foreach ($finder->files()->in($integration . '/Requests') as $request) {
                $requests[] = $request->getRealPath();
            }
        }

        return $requests;
    }

    /**
     * @return array<int, string>
     */
    protected function getIntegrationPlugins(string $integration): array
    {
        $finder = new Finder();
        $plugins = [];

        if (is_dir($integration . '/Plugins')) {
            foreach ($finder->files()->in($integration . '/Plugins') as $plugin) {
                $plugins[] = $plugin->getRealPath();
            }
        }

        return $plugins;
    }

    /**
     * @return array<int, string>
     */
    protected function getIntegrationResponses(string $integration): array
    {
        $finder = new Finder();
        $responses = [];

        if (is_dir($integration . '/Responses')) {
            foreach ($finder->files()->in($integration . '/Responses') as $response) {
                $responses[] = $response->getRealPath();
            }
        }

        return $responses;
    }

    /**
     * @return array<int, string>
     */
    protected function getIntegrationAuthenticators(string $integration): array
    {
        $finder = new Finder();
        $authenticators = [];

        if (is_dir($integration . '/Auth')) {
            foreach ($finder->files()->in($integration . '/Auth') as $authenticator) {
                $authenticators[] = $authenticator->getRealPath();
            }
        }

        return $authenticators;
    }

    protected function getIntegrationOutput(string $integration): void
    {
        $this->components->twoColumnDetail(
            '<fg=green;options=bold>' . Str::afterLast($integration, '/') . '</>',
            sprintf(
                '<fg=white>Authenticators: %s / Connectors: %s / Requests: %s / Plugins: %s / Responses: %s</>',
                count($this->getIntegrationAuthenticators($integration)),
                count($this->getIntegrationConnectors($integration)),
                count($this->getIntegrationRequests($integration)),
                count($this->getIntegrationPlugins($integration)),
                count($this->getIntegrationResponses($integration)),
            )
        );
    }

    protected function getIntegrationAuthenticatorOutput(string $authenticator): void
    {
        $this->components->twoColumnDetail(
            '<fg=red>Authenticator</> <fg=gray>...</> ' . $authenticator
        );
    }

    protected function getIntegrationConnectorOutput(string $connector): void
    {
        $this->components->twoColumnDetail(
            '<fg=blue>Connector</> <fg=gray>.......</> ' . $connector,
            '<fg=gray>' . $this->getIntegrationConnectorBaseUrl($connector) . '</>'
        );
    }

    protected function getIntegrationRequestOutput(string $request): void
    {
        $requestMethod = Str::afterLast($this->getIntegrationRequestMethod($request), ':');

        $requestMethodOutputColour = match ($requestMethod) {
            'GET' => 'blue',
            'PATCH', 'POST', 'PUT' => 'green',
            'DELETE' => 'red',
            default => 'magenta'
        };

        $this->components->twoColumnDetail(
            '<fg=magenta>Request</> <fg=gray>.........</> ' .
            $request,
            ' <fg=gray>' . $this->getIntegrationRequestEndpoint($request) . '</>' .
            ' <fg=' . $requestMethodOutputColour . '>' .
            Str::afterLast($this->getIntegrationRequestMethod($request), ':') . '</> '
        );
    }


    protected function getIntegrationPluginOutput(string $plugin): void
    {
        $this->components->twoColumnDetail(
            '<fg=cyan>Plugin</> <fg=gray>..........</> ' . $plugin
        );
    }


    protected function getIntegrationResponseOutput(string $response): void
    {
        $this->components->twoColumnDetail(
            '<fg=yellow>Response</> <fg=gray>........</> ' . $response
        );
    }

    protected function getIntegrationRequestMethod(string $request): string
    {
        $contents = file_get_contents($request);

        if ($contents === false) {
            $contents = '';
        }

        return Str::match('/\$method\s*=\s*(.*?);/', $contents);
    }


    protected function getIntegrationRequestEndpoint(string $request): string
    {
        $contents = file_get_contents($request);

        if ($contents === false) {
            $contents = '';
        }

        $regex = '/public\s+function\s+resolveEndpoint\(\):\s+string\s*\{\s*return\s+(.*?);/s';

        $match = Str::match($regex, $contents);
        $matchSegments = explode('/', $match);

        foreach ($matchSegments as $key => $matchSegment) {
            if (Str::contains($matchSegment, '$this->')) {
                $matchSegments[$key] = '{' . Str::before(
                    Str::after(str_replace(' ', '', $matchSegment), '>'),
                    '.\''
                ) . '}';
            }
        }

        return str_replace('\'', '', implode('/', $matchSegments));
    }


    protected function getIntegrationConnectorBaseUrl(string $connector): string
    {
        $contents = file_get_contents($connector);

        if ($contents === false) {
            $contents = '';
        }

        $regex = '/public\s+function\s+resolveBaseUrl\(\):\s+string\s*\{\s*return\s+\'(.*?)\';\s*/s';
        $matches = Str::match($regex, $contents);

        return Str::after($matches, '://');
    }
}
