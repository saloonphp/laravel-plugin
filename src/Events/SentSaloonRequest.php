<?php declare(strict_types=1);

namespace Sammyjo20\SaloonLaravel\Events;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

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
