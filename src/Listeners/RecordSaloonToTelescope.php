<?php

namespace Sammyjo20\SaloonLaravel\Listeners;

use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\SaloonLaravel\Events\SentSaloonRequest;

class RecordSaloonToTelescope
{
    /**
     * Handle the event.
     *
     * @param SentSaloonRequest $event
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function handle(SentSaloonRequest $event): void
    {
        $request = $event->request;
        $response = $event->response;

        // We'll attempt to guess if the response is JSON.

        $isJsonResponse = (bool)json_decode($response->body(), true);

        // Let's now append any query parameters to the end of the URL, since Saloon's getFullRequestUrl
        // does not provide it.

        $fullUrl = $this->buildFullUrl($request);

        // We'll now record the client request!

        $telescope = app()->make(Telescope::class);

        $connectorClass = get_class($request->getConnector());
        $requestClass = get_class($request);

        $entry = IncomingEntry::make([
            'method' => $request->getMethod(),
            'uri' => $fullUrl,
            'headers' => $request->getHeaders(),
            'payload' => $request->getData(),
            'response_status' => $response->status(),
            'response_headers' => $response->headers(),
            'response' => $isJsonResponse ? $response->json() : $response->body(),
            'saloon_connector' => $connectorClass,
            'saloon_request' => $requestClass,
        ]);

        // Create tags

        $entry->tags([
            'Saloon',
            'SaloonConnector:' . $connectorClass,
            'SaloonRequest:' . $requestClass,
        ]);

        $telescope->recordClientRequest($entry);
    }

    /**
     * Build the full URL with query parameters
     *
     * @param SaloonRequest $request
     * @return string
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    private function buildFullUrl(SaloonRequest $request): string
    {
        $url = $request->getFullRequestUrl();
        $glue = str_contains($url, '?') ? '&' : '?';
        $query = http_build_query($request->getQuery());

        return empty($query) ? $url : ($url . $glue . $query);
    }
}
