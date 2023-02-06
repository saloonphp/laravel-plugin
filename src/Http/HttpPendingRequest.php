<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;

class HttpPendingRequest extends PendingRequest
{
    /**
     * Constructor
     *
     * @param \Illuminate\Http\Client\Factory|null $factory
     */
    public function __construct(Factory $factory = null)
    {
        parent::__construct($factory);

        $this->options = [];
    }

    /**
     * Set the pending body on the HTTP request.
     *
     * @param string $pendingBody
     * @return $this
     */
    public function setPendingBody(string $pendingBody): static
    {
        $this->pendingBody = $pendingBody;

        return $this;
    }
}
