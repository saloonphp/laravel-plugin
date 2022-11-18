<?php declare(strict_types=1);

namespace Saloon\Laravel\Events;

use Saloon\Http\SaloonRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Saloon\Contracts\SaloonResponse;
use Saloon\Http\PendingSaloonRequest;

class SentSaloonRequest
{
    use Dispatchable;

    /**
     * The outgoing SaloonRequest
     *
     * @var PendingSaloonRequest
     */
    public PendingSaloonRequest $pendingRequest;

    /**
     * The response received by Saloon.
     *
     * @var SaloonResponse
     */
    public SaloonResponse $response;

    /**
     * Create a new event instance.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param SaloonResponse $response
     */
    public function __construct(PendingSaloonRequest $pendingRequest, SaloonResponse $response)
    {
        $this->pendingRequest = $pendingRequest;
        $this->response = $response;
    }
}
