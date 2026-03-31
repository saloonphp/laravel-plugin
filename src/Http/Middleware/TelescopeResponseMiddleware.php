<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\Response;
use Saloon\Laravel\Saloon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Saloon\Http\PendingRequest;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\IncomingEntry;
use Psr\Http\Message\ResponseInterface;
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
        $duration = $startTime !== null ? (int) ((microtime(true) - $startTime) * 1000) : null;

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
        if (! Telescope::isRecording()) {
            return;
        }

        $psrRequest = $pendingRequest->createPsrRequest();
        $psrResponse = $response->getPsrResponse();

        $rawRequestBody = (string) $psrRequest->getBody();
        $requestContentType = $psrRequest->getHeaderLine('Content-Type');
        $formattedRequestBody = self::formatBody($rawRequestBody, $requestContentType);

        $rawResponseBody = (string) $psrResponse->getBody();
        $responseContentType = $psrResponse->getHeaderLine('Content-Type');
        $formattedResponseBody = self::formatBody($rawResponseBody, $responseContentType);

        // Record to Telescope using IncomingEntry
        $entry = IncomingEntry::make([
            'method' => $psrRequest->getMethod(),
            'uri' => (string) $psrRequest->getUri(),
            'headers' => $this->sanitizeHeaders($psrRequest->getHeaders()),
            'payload' => $this->sanitizePayload($formattedRequestBody),
            'response_status' => $psrResponse->getStatusCode(),
            'response_headers' => $this->sanitizeHeaders($psrResponse->getHeaders()),
            'response' => $this->sanitizeResponseBody($formattedResponseBody, $rawResponseBody, $psrResponse),
            'duration' => $duration,
        ])->tags(['saloon']);

        Telescope::recordClientRequest($entry);
    }

    /**
     * Normalize PSR-7 headers and apply the same redaction as ClientRequestWatcher::headers().
     *
     * @param  array<string, array<int, string>>  $psrHeaders
     * @return array<string, string>
     */
    protected function sanitizeHeaders(array $psrHeaders): array
    {
        if ($psrHeaders === []) {
            return [];
        }

        $names = [];
        $values = [];

        foreach ($psrHeaders as $name => $headerValues) {
            $names[] = mb_strtolower($name);
            $values[] = implode(', ', $headerValues);
        }

        /** @var array<string, string> $combined */
        $combined = array_combine($names, $values);

        return $this->hideParameters($combined, Telescope::$hiddenRequestHeaders);
    }

    /**
     * Apply Telescope::$hiddenRequestParameters like ClientRequestWatcher::payload().
     *
     * @param  array<string, mixed>|string  $formatted
     * @return array<string, mixed>|string
     */
    protected function sanitizePayload(array|string $formatted): array|string
    {
        if (is_array($formatted)) {
            return $this->hideParameters($formatted, Telescope::$hiddenRequestParameters);
        }

        if ($formatted === '') {
            return '';
        }

        $decoded = json_decode($formatted, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->hideParameters($decoded, Telescope::$hiddenRequestParameters);
        }

        return $formatted;
    }

    /**
     * Match ClientRequestWatcher::response() for JSON, plain text, redirects, and size limits.
     *
     * @param  array<string, mixed>|string  $formatted
     * @return array<string, mixed>|string
     */
    protected function sanitizeResponseBody(array|string $formatted, string $rawBody, ResponseInterface $psrResponse): array|string
    {
        $contentType = mb_strtolower($psrResponse->getHeaderLine('Content-Type'));

        if (is_array($formatted)) {
            return $this->contentWithinLimits($rawBody)
                ? $this->hideParameters($formatted, Telescope::$hiddenResponseParameters)
                : 'Purged By Telescope';
        }

        if (Str::startsWith($contentType, 'text/plain')) {
            return $this->contentWithinLimits($formatted) ? $formatted : 'Purged By Telescope';
        }

        if ($psrResponse->getStatusCode() >= 300 && $psrResponse->getStatusCode() < 400) {
            $location = $psrResponse->getHeaderLine('Location');

            return $location !== '' ? 'Redirected to '.$location : 'Redirected';
        }

        if ($formatted === '') {
            return 'Empty Response';
        }

        return 'HTML Response';
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $hidden
     * @return array<string, mixed>
     */
    protected function hideParameters(array $data, array $hidden): array
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }

    protected function contentWithinLimits(string $content): bool
    {
        $limit = 64;

        return mb_strlen($content) / 1000 <= $limit;
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
                return is_array($decoded) ? $decoded : (string) $decoded;
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
