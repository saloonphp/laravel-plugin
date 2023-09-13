<?php

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Contracts\RequestMiddleware;
use Saloon\Contracts\ResponseMiddleware;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Saloon\Laravel\Facades\Saloon;

class RecordResponse implements ResponseMiddleware
{
    /**
     * Register a response middleware
     */
    public function __invoke(Response $response): void
    {
        Saloon::recordResponse($response);
    }
}
