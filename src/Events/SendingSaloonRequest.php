<?php

namespace Sammyjo20\SaloonLaravel\Events;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Illuminate\Foundation\Events\Dispatchable;

class SendingSaloonRequest
{
    use Dispatchable;

    /**
     * The outgoing SaloonRequest
     *
     * @var PendingSaloonRequest
     */
    public PendingSaloonRequest $pendingRequest;

    /**
     * Create a new event instance.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function __construct(PendingSaloonRequest $pendingRequest)
    {
        $this->pendingRequest = $pendingRequest;
    }
}
