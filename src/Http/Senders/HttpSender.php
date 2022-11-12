<?php

namespace Sammyjo20\SaloonLaravel\Http\Senders;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\PsrResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Http\Senders\GuzzleSender;
use Sammyjo20\Saloon\Repositories\Body\FormBodyRepository;
use Sammyjo20\Saloon\Repositories\Body\JsonBodyRepository;
use Sammyjo20\Saloon\Repositories\Body\MultipartBodyRepository;
use Sammyjo20\Saloon\Repositories\Body\StringBodyRepository;
use Sammyjo20\SaloonLaravel\Http\HttpPendingRequest;

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
        $laravelPendingRequest = new HttpPendingRequest;
        $laravelPendingRequest->setClient($this->client);

        $laravelPendingRequest->async($asynchronous);

        $this->pushHandlers($laravelPendingRequest);

        // Depending on the body format (if set) then we will specify the
        // body format on the pending request. This helps it determine
        // the Guzzle options to apply.

        // We must set the pending body if the form type is a pending body.

        // Todo: Make sure it works for json, multipart, form_params and body

        $body = $pendingRequest->body();

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
