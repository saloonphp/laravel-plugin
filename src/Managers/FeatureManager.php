<?php

namespace Sammyjo20\SaloonLaravel\Managers;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Managers\LaravelManager;
use Sammyjo20\SaloonLaravel\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;

class FeatureManager
{
    /**
     * The LaravelManager provided by Saloon.
     *
     * @var LaravelManager
     */
    protected LaravelManager $laravelManager;

    /**
     * The SaloonRequest provided by Saloon
     *
     * @var SaloonRequest
     */
    protected SaloonRequest $request;

    /**
     * @param LaravelManager $laravelManager
     * @param SaloonRequest $request
     */
    public function __construct(LaravelManager $laravelManager, SaloonRequest $request)
    {
        $this->laravelManager = $laravelManager;
        $this->request = $request;
    }

    /**
     * Boot the mocking feature.
     *
     * @return void
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException
     */
    public function bootMockingFeature(): void
    {
        $mockClient = MockClient::resolve();

        if ($mockClient->isMocking() === false) {
            return;
        }

        if ($mockClient->isEmpty()) {
            throw new SaloonNoMockResponsesProvidedException;
        }

        $this->laravelManager->setMockClient($mockClient);
    }

    /**
     * Return the Laravel Manager.
     *
     * @return LaravelManager
     */
    public function getLaravelManager(): LaravelManager
    {
        return $this->laravelManager;
    }

    /**
     * Return the Saloon request
     *
     * @return SaloonRequest
     */
    public function getRequest(): SaloonRequest
    {
        return $this->request;
    }
}
