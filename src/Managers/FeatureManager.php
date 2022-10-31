<?php

namespace Sammyjo20\SaloonLaravel\Managers;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Sammyjo20\Saloon\Managers\LaravelManager;
use Sammyjo20\SaloonLaravel\Clients\MockClient;
use Sammyjo20\SaloonLaravel\Events\SentSaloonRequest;
use Sammyjo20\SaloonLaravel\Events\SendingSaloonRequest;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;

class FeatureManager
{
    /**
     * The PendingSaloonRequest
     *
     * @var PendingSaloonRequest
     */
    protected PendingSaloonRequest $pendingRequest;

    /**
     * Constructor
     *
     * @param PendingSaloonRequest $pendingRequest
     */
    public function __construct(PendingSaloonRequest $pendingRequest)
    {
        $this->pendingRequest = $pendingRequest;
    }

    /**
     * Boot the mocking feature.
     *
     * @return self
     * @throws SaloonNoMockResponsesProvidedException
     */
    public function bootMockingFeature(): static
    {
        $pendingRequest = $this->pendingRequest;
        $mockClient = MockClient::resolve();

        if ($mockClient->isMocking() === false) {
            return $this;
        }

        if ($pendingRequest->isMocking()) {
            return $this;
        }

        if ($mockClient->isEmpty()) {
            throw new SaloonNoMockResponsesProvidedException;
        }

        $pendingRequest->setMockClient($mockClient);

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

        $this->pendingRequest->middleware()->onResponse(function (SaloonResponse $response) {
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
        $pendingRequest = $this->pendingRequest;

        // We'll firstly send off the initial event which is when the request is
        // being sent.

        SendingSaloonRequest::dispatch($pendingRequest);

        // Next, we'll register a response interceptor which will send the event
        // when a response has been received.

        $pendingRequest->middleware()->onResponse(function (SaloonResponse $response) {
            SentSaloonRequest::dispatch($response->getPendingSaloonRequest(), $response);

            return $response;
        });

        return $this;
    }
}
