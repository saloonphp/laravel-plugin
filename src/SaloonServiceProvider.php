<?php

declare(strict_types=1);

namespace Saloon\Laravel;

use Saloon\Helpers\Config;
use Saloon\Contracts\Sender;
use Illuminate\Support\ServiceProvider;
use Saloon\Laravel\Http\Faking\MockClient;
use Saloon\Laravel\Console\Commands\MakePlugin;
use Saloon\Laravel\Console\Commands\MakeRequest;
use Saloon\Laravel\Console\Commands\MakeResponse;
use Saloon\Laravel\Console\Commands\MakeConnector;
use Saloon\Laravel\Http\Middleware\LaravelMiddleware;
use Saloon\Laravel\Console\Commands\MakeAuthenticator;
use Saloon\Laravel\Console\Commands\MakeOAuthConnector;

class SaloonServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/saloon.php',
            'saloon'
        );
    }

    /**
     * Handle the booting of the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->bind('saloon', Saloon::class);
        $this->app->singleton(MockClient::class, fn () => new MockClient);

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        $this->publishes([
            __DIR__ . '/../config/saloon.php' => config_path('saloon.php'),
        ], 'saloon-config');

        // Register Saloon Laravel's Global Middleware

        if (! Saloon::$registeredDefaults) {
            Config::resolveSenderWith(static fn (): Sender => new (config('saloon.default_sender')));
            Config::middleware()->onRequest(new LaravelMiddleware, false, 'laravelMiddleware');

            Saloon::$registeredDefaults = true;
        }
    }

    protected function registerCommands(): self
    {
        $this->commands([
            MakeConnector::class,
            MakeOAuthConnector::class,
            MakeRequest::class,
            MakeResponse::class,
            MakePlugin::class,
            MakeAuthenticator::class,
        ]);

        return $this;
    }
}
