<?php

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Contracts\RequestMiddleware;
use Saloon\Http\PendingRequest;
use Saloon\Laravel\Events\SendingSaloonRequest;

class SendRequestEvent implements RequestMiddleware
{
    /**
     * Register a request middleware
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        SendingSaloonRequest::dispatch($pendingRequest);
    }
}
