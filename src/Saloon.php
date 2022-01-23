<?php

namespace Sammyjo20\SaloonLaravel;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Managers\LaravelManager;
use Sammyjo20\SaloonLaravel\Managers\FeatureManager;
use Sammyjo20\SaloonLaravel\Managers\MockManager;

class Saloon
{
    /**
     * The boot method. This is called by Saloon and from within here, can push almost anything
     * into Saloon, but most important - we can push interceptors and handlers 🚀
     *
     * @param LaravelManager $laravelManager
     * @param SaloonRequest $request
     * @return LaravelManager
     */
    public static function bootLaravelFeatures(LaravelManager $laravelManager, SaloonRequest $request): LaravelManager
    {
        $manager = new FeatureManager($laravelManager, $request);

        $manager->bootMockingFeature();

        return $manager->getLaravelManager();
    }

    /**
     * Start mocking!
     *
     * @param array $responses
     * @return MockManager
     */
    public static function fake(array $responses): MockManager
    {
        return MockManager::resolve()->startMocking($responses);
    }
}
