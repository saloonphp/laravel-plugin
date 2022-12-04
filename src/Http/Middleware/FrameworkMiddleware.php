<?php declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Contracts\Response;
use Saloon\Laravel\Facades\Saloon;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RequestMiddleware;
use Saloon\Laravel\Http\Faking\MockClient;
use Saloon\Laravel\Events\SentSaloonRequest;
use Saloon\Laravel\Events\SendingSaloonRequest;
use Saloon\Exceptions\NoMockResponsesProvidedException;

class FrameworkMiddleware implements RequestMiddleware
{
    /**
     * Handle Laravel Actions
     *
     * @param \Saloon\Http\PendingRequest $pendingRequest
     * @return \Saloon\Http\PendingRequest
     * @throws \Saloon\Exceptions\NoMockResponsesProvidedException
     */
    public function __invoke(PendingRequest $pendingRequest): PendingRequest
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
     * @param \Saloon\Http\PendingRequest $pendingRequest
     * @return $this
     * @throws \Saloon\Exceptions\NoMockResponsesProvidedException
     */
    protected function bootMockingFeature(PendingRequest $pendingRequest): static
    {
        $mockClient = MockClient::resolve();

        if ($mockClient->isMocking() === false) {
            return $this;
        }

        if ($pendingRequest->hasMockClient()) {
            return $this;
        }

        if ($mockClient->isEmpty()) {
            throw new NoMockResponsesProvidedException;
        }

        $pendingRequest->withMockClient($mockClient);

        return $this;
    }

    /**
     * Boot the recording feature.
     *
     * @param PendingRequest $pendingRequest
     * @return self
     */
    protected function bootRecordingFeature(PendingRequest $pendingRequest): static
    {
        if (Saloon::isRecording() === false) {
            return $this;
        }

        $pendingRequest->middleware()->onResponse(function (Response $response): void {
            Saloon::recordResponse($response);
        }, true);

        return $this;
    }

    /**
     * Dispatch events that can be listened to on Laravel.
     *
     * @return $this
     */
    protected function bootEventTriggers(PendingRequest $pendingRequest): static
    {
        // We'll firstly send off the initial event which is when
        // the request is being sent.

        SendingSaloonRequest::dispatch($pendingRequest);

        // Next, we'll register a response middleware which will
        // send the event when a response has been received.

        $pendingRequest->middleware()->onResponse(function (Response $response): void {
            SentSaloonRequest::dispatch($response->getPendingRequest(), $response);
        }, true);

        return $this;
    }
}
