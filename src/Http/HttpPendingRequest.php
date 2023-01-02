<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http;

use Illuminate\Http\Client\PendingRequest;

class HttpPendingRequest extends PendingRequest
{
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
