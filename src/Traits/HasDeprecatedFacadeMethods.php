<?php

declare(strict_types=1);

namespace Saloon\Laravel\Traits;

use Saloon\Http\Response;

/**
 * @internal This trait is only used to present deprecations.
 */
trait HasDeprecatedFacadeMethods
{
    /**
     * Assert JSON response data was received
     *
     * @param array<string, mixed> $data
     *
     * @deprecated This method will be removed in Saloon v4.
     */
    public static function assertSentJson(string $request, array $data): void
    {
        // This trait is only used to present deprecations.
    }

    /**
     * Start Saloon recording responses.
     *
     * @deprecated This method will be removed in Saloon v4.
     */
    public function record(): void
    {
        // This trait is only used to present deprecations.
    }

    /**
     * Stop Saloon recording responses.
     *
     * @deprecated This method will be removed in Saloon v4.
     */
    public function stopRecording(): void
    {
        // This trait is only used to present deprecations.
    }

    /**
     * Check if Saloon is recording
     *
     * @deprecated This method will be removed in Saloon v4.
     */
    public function isRecording(): bool
    {
        // This trait is only used to present deprecations.
    }

    /**
     * Record a response.
     *
     * @deprecated This method will be removed in Saloon v4.
     */
    public function recordResponse(Response $response): void
    {
        // This trait is only used to present deprecations.
    }

    /**
     * Get all the recorded responses.
     *
     * @deprecated This method will be removed in Saloon v4.
     *
     * @return array<\Saloon\Http\Response>
     */
    public function getRecordedResponses(): array
    {
        // This trait is only used to present deprecations.
    }

    /**
     * Get the last response that Saloon recorded.
     *
     * @deprecated This method will be removed in Saloon v4.
     */
    public function getLastRecordedResponse(): ?Response
    {
        // This trait is only used to present deprecations.
    }
}
