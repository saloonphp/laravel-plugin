<?php

declare(strict_types=1);

namespace Saloon\Laravel;

use Saloon\Http\Response;
use Saloon\Laravel\Http\Faking\MockClient;

class Saloon
{
    /**
     * Determine if defaults have been registered
     */
    public static bool $registeredDefaults = false;

    /**
     * Determines if requests should be recorded.
     */
    protected bool $record = false;

    /**
     * An array of the recorded responses if $record is enabled.
     *
     * @var array<\Saloon\Http\Response>
     */
    protected array $recordedResponses = [];

    /**
     * Start mocking!
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $responses
     * @throws \Saloon\Exceptions\InvalidMockResponseCaptureMethodException
     */
    public static function fake(array $responses): MockClient
    {
        return MockClient::resolve()->startMocking($responses);
    }

    /**
     * Retrieve the mock client from the container
     */
    public static function mockClient(): MockClient
    {
        return MockClient::resolve();
    }

    /**
     * Assert that a given request was sent.
     *
     * @throws \ReflectionException
     */
    public static function assertSent(string|callable $value): void
    {
        static::mockClient()->assertSent($value);
    }

    /**
     * Assert that a given request was not sent.
     *
     * @throws \ReflectionException
     */
    public static function assertNotSent(string|callable $value): void
    {
        static::mockClient()->assertNotSent($value);
    }

    /**
     * Assert JSON data was sent
     *
     * @param array<string, mixed> $data
     * @throws \ReflectionException
     */
    public static function assertSentJson(string $request, array $data): void
    {
        static::mockClient()->assertSentJson($request, $data);
    }

    /**
     * Assert that nothing was sent.
     */
    public static function assertNothingSent(): void
    {
        static::mockClient()->assertNothingSent();
    }

    /**
     * Assert a request count has been met.
     */
    public static function assertSentCount(int $count): void
    {
        static::mockClient()->assertSentCount($count);
    }

    /**
     * Start Saloon recording responses.
     */
    public function record(): void
    {
        $this->record = true;
    }

    /**
     * Stop Saloon recording responses.
     */
    public function stopRecording(): void
    {
        $this->record = false;
    }

    /**
     * Check if Saloon is recording
     */
    public function isRecording(): bool
    {
        return $this->record;
    }

    /**
     * Record a response.
     */
    public function recordResponse(Response $response): void
    {
        $this->recordedResponses[] = $response;
    }

    /**
     * Get all the recorded responses.
     *
     * @return array<\Saloon\Http\Response>
     */
    public function getRecordedResponses(): array
    {
        return $this->recordedResponses;
    }

    /**
     * Get the last response that Saloon recorded.
     */
    public function getLastRecordedResponse(): ?Response
    {
        if (empty($this->recordedResponses)) {
            return null;
        }

        $lastResponse = end($this->recordedResponses);

        reset($this->recordedResponses);

        return $lastResponse;
    }
}
