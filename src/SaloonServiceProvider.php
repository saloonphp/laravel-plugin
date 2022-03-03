<?php

namespace Sammyjo20\SaloonLaravel;

use Illuminate\Support\ServiceProvider;
use Sammyjo20\SaloonLaravel\Clients\MockClient;
use Sammyjo20\SaloonLaravel\Console\Commands\MakePlugin;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeRequest;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeKeychain;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeResponse;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeConnector;

class SaloonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind('saloon', fn () => new Saloon);
        $this->app->singleton(MockClient::class, fn () => new MockClient);

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    protected function registerCommands(): self
    {
        $this->commands([
            MakeConnector::class,
            MakeRequest::class,
            MakeResponse::class,
            MakePlugin::class,
            MakeKeychain::class,
        ]);

        return $this;
    }
}
