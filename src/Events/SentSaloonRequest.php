<?php

declare(strict_types=1);

namespace Saloon\Laravel\Events;

use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Illuminate\Foundation\Events\Dispatchable;

class SentSaloonRequest
{
    use Dispatchable;

    /**
     * The outgoing Saloon PendingRequest
     */
    public PendingRequest $pendingRequest;

    /**
     * The response received by Saloon.
     */
    public Response $response;

    /**
     * Create a new event instance.
     */
    public function __construct(PendingRequest $pendingRequest, Response $response)
    {
        $this->pendingRequest = $pendingRequest;
        $this->response = $response;
    }
}
