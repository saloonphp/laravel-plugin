<?php

namespace Sammyjo20\SaloonLaravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;

class SaloonRequestSent
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
