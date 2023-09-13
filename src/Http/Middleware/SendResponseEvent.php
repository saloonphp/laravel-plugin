<?php

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Contracts\RequestMiddleware;
use Saloon\Contracts\ResponseMiddleware;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Saloon\Laravel\Events\SendingSaloonRequest;
use Saloon\Laravel\Events\SentSaloonRequest;

class SendResponseEvent implements ResponseMiddleware
{
    /**
     * Register a response middleware
     */
    public function __invoke(Response $response): void
    {
        SentSaloonRequest::dispatch($response->getPendingRequest(), $response);
    }
}
