<?php

namespace Sammyjo20\SaloonLaravel\Events;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Illuminate\Foundation\Events\Dispatchable;

class SentSaloonRequest
{
    use Dispatchable;

    /**
     * The outgoing SaloonRequest
     *
     * @var SaloonRequest
     */
    public SaloonRequest $request;

    /**
     * The response received by Saloon.
     *
     * @var SaloonResponse
     */
    public SaloonResponse $response;

    /**
     * Create a new event instance.
     *
     * @param SaloonRequest $request
     * @param SaloonResponse $response
     */
    public function __construct(SaloonRequest $request, SaloonResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
