<?php

namespace Sammyjo20\SaloonLaravel\Events;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Illuminate\Foundation\Events\Dispatchable;

class SendingSaloonRequest
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
