<?php

namespace Sammyjo20\SaloonLaravel;

use Illuminate\Support\ServiceProvider;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeRequest;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeConnector;

class SaloonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * @return $this
     */
    protected function registerCommands(): self
    {
        $this->commands([
            MakeConnector::class,
            MakeRequest::class,
        ]);

        return $this;
    }
}
