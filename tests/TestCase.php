<?php

namespace Sammyjo20\SaloonLaravel\Tests;

use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sammyjo20\SaloonLaravel\SaloonServiceProvider;

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
