<?php

namespace Sammyjo20\SaloonLaravel\Tests;

use Illuminate\Support\Collection;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Storage\EntryModel;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeServiceProvider;
use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sammyjo20\SaloonLaravel\SaloonServiceProvider;

class TestCase extends BaseTestCase
{
    public function getPackageProviders($app)
    {
        return [
            SaloonServiceProvider::class,
            TelescopeServiceProvider::class,
        ];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../vendor/laravel/telescope/database/migrations');

        $this->artisan('migrate');
    }

    /**
     * Override application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Saloon' => Saloon::class,
        ];
    }

    /**
     * Load the Telescope entries.
     *
     * @return Collection
     */
    protected function loadTelescopeEntries(): Collection
    {
        Telescope::store(app(EntriesRepository::class));

        return EntryModel::all();
    }
}
