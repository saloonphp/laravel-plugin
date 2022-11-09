<?php

namespace Sammyjo20\SaloonLaravel\Http\Senders;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\PsrResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Http\Senders\GuzzleSender;
use Sammyjo20\SaloonLaravel\Http\LaravelPendingRequest;

class HttpSender extends GuzzleSender
{
    /**
     * Denotes if we have pushed the handlers onto the client.
     *
     * @var bool
     */
    protected bool $hasPushedHandlers = false;

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
        $laravelPendingRequest = $this->createLaravelPendingRequest($asynchronous);

        $response = $laravelPendingRequest->send(
            $pendingRequest->getMethod()->value,
            $pendingRequest->getUrl(),
            $this->createRequestOptions($pendingRequest)
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
     * @param bool $asynchronous
     * @return PendingRequest
     */
    protected function createLaravelPendingRequest(bool $asynchronous = false): PendingRequest
    {
        $laravelPendingRequest = new PendingRequest;
        $laravelPendingRequest->setClient($this->client);

        $laravelPendingRequest->async($asynchronous);

        $this->pushHandlers($laravelPendingRequest);

        return $laravelPendingRequest;
    }

    /**
     * Push Laravel's handlers onto the Guzzle Handler Stack.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    protected function pushHandlers(PendingRequest $pendingRequest): void
    {
        if ($this->hasPushedHandlers === true) {
            return;
        }

        $pendingRequest->pushHandlers($this->getHandlerStack());

        $this->hasPushedHandlers = true;
    }
}
