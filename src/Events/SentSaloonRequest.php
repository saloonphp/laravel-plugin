<?php

declare(strict_types=1);

namespace Saloon\Laravel\Events;

use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;
use Illuminate\Foundation\Events\Dispatchable;

class SentSaloonRequest
{
    use Dispatchable;

    /**
     * The outgoing Saloon PendingRequest
     *
     * @var PendingRequest
     */
    public PendingRequest $pendingRequest;

    /**
     * The response received by Saloon.
     *
     * @var Response
     */
    public Response $response;

    /**
     * Create a new event instance.
     *
     * @param PendingRequest $pendingRequest
     * @param Response $response
     */
    public function __construct(PendingRequest $pendingRequest, Response $response)
    {
        $this->pendingRequest = $pendingRequest;
        $this->response = $response;
    }
}
