<?php

declare(strict_types=1);

namespace Saloon\Laravel;

use Saloon\Config;
use Saloon\Enums\PipeOrder;
use Saloon\Contracts\Sender;
use Illuminate\Support\ServiceProvider;
use Saloon\Laravel\Http\Faking\MockClient;
use Saloon\Laravel\Console\Commands\MakePlugin;
use Saloon\Laravel\Console\Commands\ListCommand;
use Saloon\Laravel\Console\Commands\MakeRequest;
use Saloon\Laravel\Console\Commands\MakeResponse;
use Saloon\Laravel\Console\Commands\MakeConnector;
use Saloon\Laravel\Http\Middleware\MockMiddleware;
use Saloon\Laravel\Http\Middleware\RecordResponse;
use Saloon\Laravel\Http\Middleware\SendRequestEvent;
use Saloon\Laravel\Http\Middleware\SendResponseEvent;
use Saloon\Laravel\Console\Commands\MakeAuthenticator;

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
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
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
            Config::setSenderResolver(static function (): Sender {
                /** @var Sender */
                return new (config('saloon.default_sender'));
            });

            Config::globalMiddleware()
                ->onRequest(new MockMiddleware, 'laravelMock')
                ->onRequest(new SendRequestEvent, 'laravelSendRequestEvent', PipeOrder::LAST)
                ->onResponse(new RecordResponse, 'laravelRecordResponse', PipeOrder::FIRST)
                ->onResponse(new SendResponseEvent, 'laravelSendResponseEvent', PipeOrder::FIRST);

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
            MakeRequest::class,
            MakeResponse::class,
            MakePlugin::class,
            MakeAuthenticator::class,
            ListCommand::class,
        ]);

        return $this;
    }
}
