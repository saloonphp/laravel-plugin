<?php

namespace Sammyjo20\SaloonLaravel\Managers;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Sammyjo20\Saloon\Managers\LaravelManager;
use Sammyjo20\SaloonLaravel\Clients\MockClient;
use Sammyjo20\SaloonLaravel\Events\SentSaloonRequest;
use Sammyjo20\SaloonLaravel\Events\SendingSaloonRequest;
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
     * @return self
     * @throws SaloonNoMockResponsesProvidedException
     */
    public function bootMockingFeature(): static
    {
        $mockClient = MockClient::resolve();

        if ($mockClient->isMocking() === false) {
            return $this;
        }

        if ($mockClient->isEmpty()) {
            throw new SaloonNoMockResponsesProvidedException;
        }

        $this->laravelManager->setMockClient($mockClient);

        return $this;
    }

    /**
     * Boot the recording feature.
     *
     * @return self
     * @throws SaloonNoMockResponsesProvidedException
     */
    public function bootRecordingFeature(): static
    {
        if (Saloon::isRecording() === false) {
            return $this;
        }

        // Register a response interceptor which will record the response.

        $this->request->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
            Saloon::recordResponse($response);

            return $response;
        });

        return $this;
    }

    /**
     * Dispatch events that can be listened to on Laravel.
     *
     * @return $this
     */
    public function bootEventTriggers(): static
    {
        $request = $this->request;

        // We'll firstly send off the initial event which is when the request is
        // being sent.

        SendingSaloonRequest::dispatch($request);

        // Next, we'll register a response interceptor which will send the event
        // when a response has been received.

        $request->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
            SentSaloonRequest::dispatch($request, $response);

            return $response;
        });

        return $this;
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
