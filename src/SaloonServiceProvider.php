<?php

declare(strict_types=1);

namespace Saloon\Laravel;

use Saloon\Config;
use Saloon\Contracts\Sender;
use Illuminate\Support\ServiceProvider;
use Saloon\Enums\PipeOrder;
use Saloon\Laravel\Http\Faking\MockClient;
use Saloon\Laravel\Console\Commands\MakePlugin;
use Saloon\Laravel\Console\Commands\MakeRequest;
use Saloon\Laravel\Console\Commands\MakeResponse;
use Saloon\Laravel\Console\Commands\MakeConnector;
use Saloon\Laravel\Http\Middleware\LaravelMiddleware;
use Saloon\Laravel\Console\Commands\MakeAuthenticator;
use Saloon\Laravel\Console\Commands\MakeOAuthConnector;
use Saloon\Laravel\Http\Middleware\MockMiddleware;
use Saloon\Laravel\Http\Middleware\RecordResponse;
use Saloon\Laravel\Http\Middleware\SendRequestEvent;
use Saloon\Laravel\Http\Middleware\SendResponseEvent;

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
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function boot(): void
    {
        $this->app->bind('saloon', Saloon::class);
        $this->app->singleton(MockClient::class, fn() => new MockClient);

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        $this->publishes([
            __DIR__ . '/../config/saloon.php' => config_path('saloon.php'),
        ], 'saloon-config');

        // Register Saloon Laravel's Global Middleware

        if (! Saloon::$registeredDefaults) {
            Config::setSenderResolver(static fn(): Sender => new (config('saloon.default_sender')));

            Config::globalMiddleware()->onRequest(new MockMiddleware, 'laravelMock', PipeOrder::FIRST);
            Config::globalMiddleware()->onRequest(new SendRequestEvent, 'laravelSendRequestEvent', PipeOrder::LAST);
            Config::globalMiddleware()->onResponse(new RecordResponse, 'laravelRecordResponse', PipeOrder::FIRST);
            Config::globalMiddleware()->onResponse(new SendResponseEvent, 'laravelSendResponseEvent', PipeOrder::FIRST);

            Saloon::$registeredDefaults = true;
        }
    }

    /**
     * Register the Saloon commands
     *
     * @return $this
     */
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
