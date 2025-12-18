<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\Response;
use Saloon\Laravel\Saloon;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\ResponseMiddleware;

class TelescopeResponseMiddleware implements ResponseMiddleware
{
    public function __invoke(Response $response): void
    {
        // Check if Telescope is installed

        if (! class_exists('Laravel\Telescope\Telescope')) {
            return;
        }

        $pendingRequest = $response->getPendingRequest();

        $requestId = spl_object_id($pendingRequest);
        $startTime = Saloon::$telescopeStartTimes[$requestId] ?? null;

        // Calculate duration
        $duration = $startTime !== null ? (int)((microtime(true) - $startTime) * 1000) : null;

        // Clean up start time
        unset(Saloon::$telescopeStartTimes[$requestId]);

        // Record to Telescope

        $this->recordToTelescope($pendingRequest, $response, $duration);
    }

    /**
     * Record the request to Telescope
     */
    protected function recordToTelescope(PendingRequest $pendingRequest, Response $response, ?int $duration): void
    {
        if (! \Laravel\Telescope\Telescope::isRecording()) {
            return;
        }

        $psrRequest = $pendingRequest->createPsrRequest();
        $psrResponse = $response->getPsrResponse();

        // Format request data
        $requestData = [
            'method' => $psrRequest->getMethod(),
            'url' => (string)$psrRequest->getUri(),
            'headers' => $psrRequest->getHeaders(),
            'body' => self::formatBody((string)$psrRequest->getBody(), $psrRequest->getHeaderLine('Content-Type')),
        ];

        // Format response data
        $responseData = [
            'status' => $psrResponse->getStatusCode(),
            'headers' => $psrResponse->getHeaders(),
            'body' => self::formatBody((string)$psrResponse->getBody(), $psrResponse->getHeaderLine('Content-Type')),
        ];

        // Record to Telescope using IncomingEntry
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
