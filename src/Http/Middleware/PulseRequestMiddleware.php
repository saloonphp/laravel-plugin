<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Laravel\Saloon;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class PulseRequestMiddleware implements RequestMiddleware
{
    public function __invoke(PendingRequest $pendingRequest): void
    {
        // Check if Pulse is installed

        if (! class_exists('Laravel\Pulse\Facades\Pulse')) {
            return;
        }

        // Record start time for duration calculation
        $requestId = spl_object_id($pendingRequest);

        Saloon::$pulseStartTimes[$requestId] = microtime(true);
    }
}
