<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests;

use Saloon\Laravel\Facades\Saloon;
use Saloon\Laravel\SaloonServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function getPackageProviders($app)
    {
        return [
            SaloonServiceProvider::class,
        ];
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
}
