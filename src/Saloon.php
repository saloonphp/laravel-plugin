<?php

namespace Sammyjo20\SaloonLaravel;

use Sammyjo20\Saloon\Managers\LaravelManger;
use Sammyjo20\SaloonLaravel\Http\Handlers\MockHandler;
use Sammyjo20\SaloonLaravel\Managers\SaloonMockManager;

class Saloon
{
    /**
     * The boot method. This is called by Saloon and from within here, can push almost anything
     * into Saloon, but most important - we can push interceptors and handlers 🚀
     *
     * @param LaravelManger $laravelManger
     * @return LaravelManger
     */
    public static function boot(LaravelManger $laravelManger): LaravelManger
    {
        $isMocking = SaloonMockManager::resolve()->isMocking();

        if ($isMocking === true) {
            $laravelManger->addHandler('laravelSaloonMockHandler', new MockHandler);
        }

        $laravelManger->setIsMocking($isMocking);

        return $laravelManger;
    }

    /**
     * Start mocking
     *
     * @param array $sequence
     * @return SaloonMockManager
     */
    public static function fake(array $sequence = []): SaloonMockManager
    {
        return SaloonMockManager::resolve()->startMocking($sequence);
    }
}
