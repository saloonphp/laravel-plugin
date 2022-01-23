<?php

namespace Sammyjo20\SaloonLaravel;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Managers\LaravelManager;
use Sammyjo20\SaloonLaravel\Clients\MockClient;
use Sammyjo20\SaloonLaravel\Managers\FeatureManager;

class Saloon
{
    /**
     * The boot method. This is called by Saloon and from within here, can push almost anything
     * into Saloon, but most important - we can push interceptors and handlers 🚀
     *
     * @param LaravelManager $laravelManager
     * @param SaloonRequest $request
     * @return LaravelManager
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException
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
     * @return MockClient
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidMockResponseCaptureMethodException
     */
    public static function fake(array $responses): MockClient
    {
        return MockClient::resolve()->startMocking($responses);
    }
}
