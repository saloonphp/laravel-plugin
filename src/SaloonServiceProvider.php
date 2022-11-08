<?php declare(strict_types=1);

namespace Sammyjo20\SaloonLaravel;

use Illuminate\Support\ServiceProvider;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeAuthenticator;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeConnector;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeOAuthConnector;
use Sammyjo20\SaloonLaravel\Console\Commands\MakePlugin;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeRequest;
use Sammyjo20\SaloonLaravel\Console\Commands\MakeResponse;
use Sammyjo20\SaloonLaravel\Http\Faking\MockClient;

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
            __DIR__.'/../config/saloon.php', 'saloon'
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
            __DIR__.'/../config/saloon.php' => config_path('saloon.php'),
        ], 'saloon-config');
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
