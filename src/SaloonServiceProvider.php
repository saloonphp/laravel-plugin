<?php

namespace Sammyjo20\SaloonLaravel;

use Sammyjo20\SaloonLaravel\Console\Commands\MakeRequest;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeConnector;
use \Sammyjo20\Saloon\Laravel\SaloonServiceProvider as BaseSaloonServiceProvider;

class SaloonServiceProvider extends BaseSaloonServiceProvider
{
    public function boot(): void
    {
        parent::boot();

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
