<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Senders;

use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Laravel\Http\HttpPendingRequest;
use Saloon\Repositories\Body\FormBodyRepository;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Repositories\Body\MultipartBodyRepository;
use Illuminate\Http\Client\Response as IlluminateResponse;

class HttpSender extends GuzzleSender
{
    /**
     * Denotes if we have registered the Laravel's default handlers onto the client.
     *
     * @var bool
     */
    protected bool $hasRegisteredDefaultHandlers = false;

    /**
     * Send the request
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface
     * @throws \Exception
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): Response|PromiseInterface
    {
        $laravelPendingRequest = $this->createLaravelPendingRequest($pendingRequest, $asynchronous);

        // We should pass in the request options as there is a call inside
        // the send method that parses the HTTP options and the Laravel
        // data properly.

        $response = $laravelPendingRequest->send(
            $pendingRequest->getMethod()->value,
            $pendingRequest->getUrl(),
            $this->createRequestOptions($pendingRequest)
        );

        // When the response is a normal HTTP Client Response, we can create the response

        if ($response instanceof IlluminateResponse) {
            return $this->createResponse($pendingRequest, $response->toPsrResponse(), $response->toException());
        }

        // Otherwise, it will be a Promise - so we will process the promise like normal.

        // Todo: Make sure it throws exceptions properly

        $promise = $response->then(fn (IlluminateResponse $response) => $response->toPsrResponse());

        return $this->processPromise($promise, $pendingRequest);
    }

    /**
     * Create the Laravel Pending Request
     *
     * @param PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return HttpPendingRequest
     */
    protected function createLaravelPendingRequest(PendingRequest $pendingRequest, bool $asynchronous = false): HttpPendingRequest
    {
        $laravelPendingRequest = new HttpPendingRequest;
        $laravelPendingRequest->setClient($this->client);

        $this->registerDefaultHandlers($laravelPendingRequest);

        if ($asynchronous === true) {
            $laravelPendingRequest->async();
        }

        // Depending on the body format (if set) then we will specify the
        // body format on the pending request. This helps it determine
        // the Guzzle options to apply.

        $body = $pendingRequest->body();

        if (is_null($body)) {
            return $laravelPendingRequest;
        }

        match (true) {
            $body instanceof JsonBodyRepository => $laravelPendingRequest->bodyFormat('json'),
            $body instanceof MultipartBodyRepository => $laravelPendingRequest->bodyFormat('multipart'),
            $body instanceof FormBodyRepository => $laravelPendingRequest->bodyFormat('form_params'),
            $body instanceof StringBodyRepository => $laravelPendingRequest->bodyFormat('body')->setPendingBody($body->all()),
            default => $laravelPendingRequest->bodyFormat('body')->setPendingBody((string)$body),
        };

        return $laravelPendingRequest;
    }

    /**
     * Register Laravel's handlers onto the Guzzle Handler Stack.
     *
     * @param HttpPendingRequest $httpPendingRequest
     * @return void
     */
    protected function registerDefaultHandlers(HttpPendingRequest $httpPendingRequest): void
    {
        if ($this->hasRegisteredDefaultHandlers === true) {
            return;
        }

        $httpPendingRequest->pushHandlers($this->getHandlerStack());

        $this->hasRegisteredDefaultHandlers = true;
    }
}
