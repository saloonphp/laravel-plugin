<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\PendingRequest;
use Illuminate\Support\Facades\Event;
use Saloon\Contracts\RequestMiddleware;
use Saloon\Laravel\Events\SentSaloonRequest;
use Saloon\Laravel\Events\SendingSaloonRequest;

class TelescopeMiddleware implements RequestMiddleware
{
    /**
     * Track start time for duration calculation
     *
     * @var array<int, float>
     */
    protected static array $startTimes = [];

    /**
     * Whether event listeners have been registered
     */
    protected static bool $listenersRegistered = false;

    /**
     * Create a new Telescope middleware instance
     */
    public function __construct()
    {
        // Register event listeners once
        if (! self::$listenersRegistered) {
            Event::listen(SendingSaloonRequest::class, [self::class, 'handleSending']);
            Event::listen(SentSaloonRequest::class, [self::class, 'handleSent']);
            self::$listenersRegistered = true;
        }
    }

    public function __invoke(PendingRequest $pendingRequest): void
    {
    }

    /**
     * Handle the SendingSaloonRequest event
     */
    public static function handleSending(SendingSaloonRequest $event): void
    {
        // Check if Telescope is installed
        if (! class_exists('Laravel\Telescope\Telescope')) {
            return;
        }

        $pendingRequest = $event->pendingRequest;

        // Record start time for duration calculation
        $requestId = spl_object_id($pendingRequest);
        self::$startTimes[$requestId] = microtime(true);
    }

    /**
     * Handle the SentSaloonRequest event
     */
    public static function handleSent(SentSaloonRequest $event): void
    {
        // Check if Telescope is installed
        if (! class_exists('Laravel\Telescope\Telescope')) {
            return;
        }

        $pendingRequest = $event->pendingRequest;
        $response = $event->response;

        $requestId = spl_object_id($pendingRequest);
        $startTime = self::$startTimes[$requestId] ?? null;

        // Calculate duration
        $duration = $startTime !== null ? (int) ((microtime(true) - $startTime) * 1000) : null;

        // Clean up start time
        unset(self::$startTimes[$requestId]);

        // Record to Telescope
        self::recordToTelescope($pendingRequest, $response, $duration);
    }

    /**
     * Record the request to Telescope
     */
    protected static function recordToTelescope(\Saloon\Http\PendingRequest $pendingRequest, \Saloon\Http\Response $response, ?int $duration): void
    {
        // @phpstan-ignore-next-line
        if (! \Laravel\Telescope\Telescope::isRecording()) {
            return;
        }

        $psrRequest = $pendingRequest->createPsrRequest();
        $psrResponse = $response->getPsrResponse();

        // Format request data
        $requestData = [
            'method' => $psrRequest->getMethod(),
            'url' => (string) $psrRequest->getUri(),
            'headers' => $psrRequest->getHeaders(),
            'body' => self::formatBody((string) $psrRequest->getBody(), $psrRequest->getHeaderLine('Content-Type')),
        ];

        // Format response data
        $responseData = [
            'status' => $psrResponse->getStatusCode(),
            'headers' => $psrResponse->getHeaders(),
            'body' => self::formatBody((string) $psrResponse->getBody(), $psrResponse->getHeaderLine('Content-Type')),
        ];

        // Record to Telescope using IncomingEntry
        // @phpstan-ignore-next-line
        $entry = \Laravel\Telescope\IncomingEntry::make([
            'method' => $requestData['method'],
            'uri' => $requestData['url'],
            'headers' => $requestData['headers'],
            'payload' => $requestData['body'],
            'response_status' => $responseData['status'],
            'response_headers' => $responseData['headers'],
            'response' => $responseData['body'],
            'duration' => $duration,
        ])->tags(['saloon']);

        // @phpstan-ignore-next-line
        \Laravel\Telescope\Telescope::recordClientRequest($entry);
    }

    /**
     * Format body for display
     *
     * @return array<string, mixed>|string
     */
    protected static function formatBody(string $body, string $contentType): array|string
    {
        if (empty($body)) {
            return '';
        }

        // Try to decode JSON
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try to decode form data
        if (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($body, $formData);

            // @phpstan-ignore-next-line - parse_str can create numeric keys but we treat as string keys
            return $formData;
        }

        // Return as string
        return $body;
    }
}
