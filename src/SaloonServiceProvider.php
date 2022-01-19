<?php

namespace Sammyjo20\SaloonLaravel;

use Illuminate\Support\ServiceProvider;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeRequest;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeConnector;
use Sammyjo20\SaloonLaravel\Managers\SaloonMockManager;

class SaloonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind('saloon', fn () => new Saloon);
        $this->app->singleton(SaloonMockManager::class, fn () => new SaloonMockManager);

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    protected function registerCommands(): self
    {
        $this->commands([
            MakeConnector::class,
            MakeRequest::class,
        ]);

        return $this;
    }
}
