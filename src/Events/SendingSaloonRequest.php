<?php

declare(strict_types=1);

namespace Saloon\Laravel\Events;

use Saloon\Contracts\PendingRequest;
use Illuminate\Foundation\Events\Dispatchable;

class SendingSaloonRequest
{
    use Dispatchable;

    /**
     * The outgoing Saloon PendingRequest
     *
     * @var PendingRequest
     */
    public PendingRequest $pendingRequest;

    /**
     * Create a new event instance.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function __construct(PendingRequest $pendingRequest)
    {
        $this->pendingRequest = $pendingRequest;
    }
}
