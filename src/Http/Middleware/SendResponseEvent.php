<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\Response;
use Saloon\Contracts\ResponseMiddleware;
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
