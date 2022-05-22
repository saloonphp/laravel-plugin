<?php

namespace Sammyjo20\SaloonLaravel\Managers;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Managers\LaravelManager;
use Sammyjo20\SaloonLaravel\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\SaloonLaravel\Facades\Saloon;

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
     * @throws SaloonNoMockResponsesProvidedException
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
     * Boot the recording feature.
     *
     * @return void
     * @throws SaloonNoMockResponsesProvidedException
     */
    public function bootRecordingFeature(): void
    {
        if (Saloon::isRecording() === false) {
            return;
        }

        // Register a response interceptor which will record the response.

        $this->request->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
            Saloon::recordResponse($response);

            return $response;
        });
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
