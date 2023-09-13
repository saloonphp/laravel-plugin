<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\Response;
use Saloon\Laravel\Facades\Saloon;
use Saloon\Contracts\ResponseMiddleware;

class RecordResponse implements ResponseMiddleware
{
    /**
     * Register a response middleware
     */
    public function __invoke(Response $response): void
    {
        if (Saloon::isRecording() === false) {
            return;
        }

        Saloon::recordResponse($response);
    }
}
