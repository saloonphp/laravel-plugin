<?php declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Contracts\RequestMiddleware;
use Saloon\Contracts\SaloonResponse;
use Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Saloon\Http\PendingSaloonRequest;
use Saloon\Laravel\Events\SendingSaloonRequest;
use Saloon\Laravel\Events\SentSaloonRequest;
use Saloon\Laravel\Facades\Saloon;
use Saloon\Laravel\Http\Faking\MockClient;
use Saloon\Laravel\Managers\FeatureManager;

class SaloonLaravelMiddleware implements RequestMiddleware
{
    /**
     * Handle Laravel Actions
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return PendingSaloonRequest
     * @throws SaloonNoMockResponsesProvidedException
     */
    public function __invoke(PendingSaloonRequest $pendingRequest): PendingSaloonRequest
    {
        $this
            ->bootMockingFeature($pendingRequest)
            ->bootRecordingFeature($pendingRequest)
            ->bootEventTriggers($pendingRequest);

        return $pendingRequest;
    }

    /**
     * Boot the mocking feature.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return self
     * @throws SaloonNoMockResponsesProvidedException
     */
    protected function bootMockingFeature(PendingSaloonRequest $pendingRequest): static
    {
        $mockClient = MockClient::resolve();

        if ($mockClient->isMocking() === false) {
            return $this;
        }

        if ($pendingRequest->hasMockClient()) {
            return $this;
        }

        if ($mockClient->isEmpty()) {
            throw new SaloonNoMockResponsesProvidedException;
        }

        $pendingRequest->withMockClient($mockClient);

        return $this;
    }

    /**
     * Boot the recording feature.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return self
     */
    protected function bootRecordingFeature(PendingSaloonRequest $pendingRequest): static
    {
        if (Saloon::isRecording() === false) {
            return $this;
        }

        $pendingRequest->middleware()->onResponse(function (SaloonResponse $response): void {
            Saloon::recordResponse($response);
        });

        return $this;
    }

    /**
     * Dispatch events that can be listened to on Laravel.
     *
     * @return $this
     */
    protected function bootEventTriggers(PendingSaloonRequest $pendingRequest): static
    {
        // We'll firstly send off the initial event which is when
        // the request is being sent.

        SendingSaloonRequest::dispatch($pendingRequest);

        // Next, we'll register a response middleware which will
        // send the event when a response has been received.

        $pendingRequest->middleware()->onResponse(function (SaloonResponse $response): void {
            SentSaloonRequest::dispatch($response->getPendingSaloonRequest(), $response);
        });

        return $this;
    }
}
