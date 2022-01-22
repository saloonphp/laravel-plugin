<?php

namespace Sammyjo20\SaloonLaravel;

use Sammyjo20\Saloon\Managers\LaravelManager;
use Sammyjo20\SaloonLaravel\Http\Handlers\MockHandler;
use Sammyjo20\SaloonLaravel\Managers\SaloonMockManager;

class Saloon
{
    /**
     * The boot method. This is called by Saloon and from within here, can push almost anything
     * into Saloon, but most important - we can push interceptors and handlers 🚀
     *
     * @param LaravelManager $laravelManager
     * @return LaravelManager
     */
    public static function boot(LaravelManager $laravelManager): LaravelManager
    {
        $isMocking = SaloonMockManager::resolve()->isMocking();

        if ($isMocking === true) {
            $laravelManager->addHandler('laravelSaloonMockHandler', new MockHandler);
            // Todo: Use a response interceptor
        }

        $laravelManager->setIsMocking($isMocking);

        return $laravelManager;
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
