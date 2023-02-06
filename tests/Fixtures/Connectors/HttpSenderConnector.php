<?php

declare(strict_types=1);

namespace Saloon\Laravel\Tests\Fixtures\Connectors;

use Saloon\Contracts\Sender;
use Saloon\Http\Connector;
use Saloon\Laravel\Http\Senders\HttpSender;
use Saloon\Traits\Plugins\AcceptsJson;

class HttpSenderConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Define the base headers that will be applied in every request.
     *
     * @return string[]
     */
    protected function defaultHeaders(): array
    {
        return [];
    }

    /**
     * Configure the default sender
     *
     * @return \Saloon\Contracts\Sender
     */
    protected function defaultSender(): Sender
    {
        return new HttpSender;
    }
}
