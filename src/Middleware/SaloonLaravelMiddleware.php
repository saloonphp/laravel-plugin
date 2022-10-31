<?php

namespace Sammyjo20\SaloonLaravel\Middleware;

use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Helpers\Pipeline;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class SaloonLaravelMiddleware
{
    /**
     * Handle Laravel Actions
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param Pipeline $middleware
     * @return PendingSaloonRequest
     */
    public function __invoke(PendingSaloonRequest $pendingRequest, MiddlewarePipeline $middleware): PendingSaloonRequest
    {
        // Right, so inside here we can make changes to the pending request
        // But how are we going to change the mock client and stuff?
        // Unless this middleware runs before the mock client one?

        // Todo: Set the mock client if Saloon Laravel is mocking and the pending request isn't already mocking
        // Todo: Register middleware to record responses and trigger events

        dd($middleware);

        $middleware
            ->onRequest(function (PendingSaloonRequest $pendingRequest) {
                dd('I am inside another method', $pendingRequest);
            });

        return $pendingRequest;
    }
}
