<?php

namespace Sammyjo20\SaloonLaravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Sammyjo20\Saloon\Http\SaloonRequest;

class SaloonRequestSending
{
    use Dispatchable;

    /**
     * The outgoing SaloonRequest
     *
     * @var SaloonRequest
     */
    public SaloonRequest $request;

    /**
     * Create a new event instance.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function __construct(SaloonRequest $request)
    {
        $this->request = $request;
    }
}
