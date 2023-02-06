<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Senders;

use Illuminate\Support\Facades\Http;
use Throwable;
use Saloon\Contracts\Response;
use Illuminate\Http\Client\Factory;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Saloon\Laravel\Http\HttpPendingRequest;
use Saloon\Repositories\Body\FormBodyRepository;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;
use Illuminate\Http\Client\Response as HttpResponse;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Repositories\Body\MultipartBodyRepository;
use Illuminate\Http\Client\RequestException as HttpRequestException;

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
        try {
            $laravelPendingRequest = $this->createLaravelPendingRequest($pendingRequest, $asynchronous);

            // We should pass in the request options as there is a call inside
            // the send method that parses the HTTP options and the Laravel
            // data properly.

            $response = $laravelPendingRequest->send(
                $pendingRequest->getMethod()->value,
                $pendingRequest->getUrl(),
                $this->createRequestOptions($pendingRequest)
            );
        } catch (TransferException $exception) {
            if ($pendingRequest->isAsynchronous() === false) {
                // When the exception wasn't a RequestException, we'll throw a fatal
                // exception as this is likely a ConnectException, but it will
                // catch any new ones Guzzle release.

                if (! $exception instanceof RequestException) {
                    throw new FatalRequestException($exception, $pendingRequest);
                }

                // Otherwise, we'll create a response.

                return $this->createResponse($pendingRequest, $exception->getResponse(), $exception);
            }
        }

        // When the response is a normal HTTP Client Response, we can create the response

        return $response instanceof HttpResponse
            ? $this->createResponse($pendingRequest, $response->toPsrResponse(), $response->toException())
            : $this->processPromise($response, $pendingRequest);
    }

    /**
     * Process the promise
     *
     * @param \GuzzleHttp\Promise\PromiseInterface $promise
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    protected function processPromise(PromiseInterface $promise, PendingRequest $pendingRequest): PromiseInterface
    {
        // When it comes to promises, it's a little tricky because of Laravel's built-in
        // exception handler which always converts a request exception into a response.
        // Here we will undo that functionality by catching the exception and throwing
        // it back down the "otherwise"/"catch" chain

        return $promise
            ->then(function (HttpResponse|TransferException $result) {
                $exception = $result instanceof TransferException ? $result : $result->toException();

                if ($exception instanceof Throwable) {
                    throw $exception;
                }

                return $result;
            })
            ->then(
                function (HttpResponse $response) use ($pendingRequest) {
                    return $this->createResponse($pendingRequest, $response->toPsrResponse());
                },
            )
            ->otherwise(
                function (HttpRequestException|TransferException $exception) use ($pendingRequest) {
                    // When the exception wasn't a HttpRequestException, we'll throw a fatal
                    // exception as this is likely a ConnectException, but it will
                    // catch any new ones Guzzle release.

                    if (! $exception instanceof HttpRequestException) {
                        throw new FatalRequestException($exception, $pendingRequest);
                    }

                    // Otherwise we'll create a response to convert into an exception.
                    // This will run the exception through the exception handlers
                    // which allows the user to handle their own exceptions.

                    $response = $this->createResponse($pendingRequest, $exception->response->toPsrResponse(), $exception);

                    // Throw the exception our way

                    throw $response->toException();
                }
            );
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
        $httpPendingRequest = new HttpPendingRequest(resolve(Factory::class));
        $httpPendingRequest->setClient($this->client);

        $this->registerDefaultHandlers($httpPendingRequest);

        if ($asynchronous === true) {
            $httpPendingRequest->async();
        }

        // Depending on the body format (if set) then we will specify the
        // body format on the pending request. This helps it determine
        // the Guzzle options to apply.

        $body = $pendingRequest->body();

        if (is_null($body)) {
            return $httpPendingRequest;
        }

        match (true) {
            $body instanceof JsonBodyRepository => $httpPendingRequest->bodyFormat('json'),
            $body instanceof MultipartBodyRepository => $httpPendingRequest->bodyFormat('multipart'),
            $body instanceof FormBodyRepository => $httpPendingRequest->bodyFormat('form_params'),
            $body instanceof StringBodyRepository => $httpPendingRequest->bodyFormat('body')->setPendingBody($body->all()),
            default => $httpPendingRequest->bodyFormat('body')->setPendingBody((string)$body),
        };

        return $httpPendingRequest;
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
