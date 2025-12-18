<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Laravel\Saloon;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class TelescopeRequestMiddleware implements RequestMiddleware
{
    public function __invoke(PendingRequest $pendingRequest): void
    {
        // Check if Telescope is installed

        if (! class_exists('Laravel\Telescope\Telescope')) {
            return;
        }

        // Record start time for duration calculation
        $requestId = spl_object_id($pendingRequest);

        Saloon::$telescopeStartTimes[$requestId] = microtime(true);
    }
}
