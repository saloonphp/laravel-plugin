<?php

namespace Sammyjo20\SaloonLaravel\Listeners;

use Illuminate\Support\Str;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
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

        $responseIsJson = Str::of($response->header('Content-Type'))->lower()->contains('application/json');

        // Let's now append any query parameters to the end of the URL, since Saloon's getFullRequestUrl
        // does not provide it.

        $url = $request->getFullRequestUrl();
        $glue = str_contains($url, '?') ? '&' : '?';
        $query = http_build_query($request->getQuery());

        $fullUrl = empty($query) ? $url : ($url . $glue . $query);

        // We'll now record the client request!

        $telescope = app()->make(Telescope::class);

        $entry = IncomingEntry::make([
            'method' => $request->getMethod(),
            'uri' => $fullUrl,
            'headers' => $request->getHeaders(),
            'payload' => $request->getData(),
            'response_status' => $response->status(),
            'response_headers' => $response->headers(),
            'response' => $responseIsJson ? $response->json() : $response->body(),
            'saloon_connector' => get_class($request->getConnector()),
            'saloon_request' => get_class($request),
        ]);

        $entry->tags([
            'Saloon',
            'SaloonConnector:' . get_class($request->getConnector()),
            'SaloonRequest:' . get_class($request),
        ]);

        $telescope->recordClientRequest($entry);
    }
}
