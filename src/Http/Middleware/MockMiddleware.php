<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\RequestMiddleware;
use Saloon\Laravel\Http\Faking\MockClient;
use Saloon\Exceptions\NoMockResponseFoundException;

class MockMiddleware implements RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @throws \Saloon\Exceptions\NoMockResponseFoundException
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $mockClient = MockClient::resolve();

        if ($mockClient->isMocking() === false) {
            return;
        }

        if ($pendingRequest->hasMockClient() || $pendingRequest->hasFakeResponse()) {
            return;
        }

        if ($mockClient->isEmpty()) {
            throw new NoMockResponseFoundException($pendingRequest);
        }

        $pendingRequest->withMockClient($mockClient);
    }
}
