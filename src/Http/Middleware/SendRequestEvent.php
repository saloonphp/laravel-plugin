<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\RequestMiddleware;
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
