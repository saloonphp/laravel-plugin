<?php

namespace Sammyjo20\SaloonLaravel\Http\Senders;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\PsrResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Http\Senders\GuzzleSender;

class HttpSender extends GuzzleSender
{
    /**
     * Send the request
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     * @throws \Exception
     */
    public function sendRequest(PendingSaloonRequest $pendingRequest, bool $asynchronous = false): PsrResponse|PromiseInterface
    {
        $laravelPendingRequest = $this->createLaravelPendingRequest($pendingRequest, $asynchronous);

        $response = $laravelPendingRequest->send(
            $pendingRequest->getMethod()->value,
            $pendingRequest->getUrl()
        );

        // When the response is a normal HTTP Client Response, we can create the response

        if ($response instanceof Response) {
            return $this->createResponse($pendingRequest, $response->toPsrResponse(), $response->toException());
        }

        // Otherwise, it will be a Promise - so we will process the promise like normal.

        $promise = $response->then(fn (Response $response) => $response->toPsrResponse());

        return $this->processPromise($promise, $pendingRequest);
    }

    /**
     * Create the Laravel Pending Request
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asynchronous
     * @return PendingRequest
     */
    protected function createLaravelPendingRequest(PendingSaloonRequest $pendingRequest, bool $asynchronous = false): PendingRequest
    {
        $laravelPendingRequest = new PendingRequest();
        $laravelPendingRequest->setClient($this->client);

        $laravelPendingRequest->withOptions(
            $this->createRequestOptions($pendingRequest)
        );

        $laravelPendingRequest->async($asynchronous);

        return $laravelPendingRequest;
    }
}
