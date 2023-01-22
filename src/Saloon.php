<?php

declare(strict_types=1);

namespace Saloon\Laravel;

use Saloon\Contracts\Response;
use Saloon\Laravel\Http\Faking\MockClient;

class Saloon
{
    /**
     * Determine if defaults have been registered
     *
     * @var bool
     */
    public static bool $registeredDefaults = false;

    /**
     * Determines if requests should be recorded.
     *
     * @var bool
     */
    protected bool $record = false;

    /**
     * An array of the recorded responses if $record is enabled.
     *
     * @var array
     */
    protected array $recordedResponses = [];

    /**
     * Start mocking!
     *
     * @param array $responses
     * @return \Saloon\Laravel\Http\Faking\MockClient
     * @throws \Saloon\Exceptions\InvalidMockResponseCaptureMethodException
     */
    public static function fake(array $responses): MockClient
    {
        return MockClient::resolve()->startMocking($responses);
    }

    /**
     * Retrieve the mock client from the container
     *
     * @return MockClient
     */
    public static function mockClient(): MockClient
    {
        return MockClient::resolve();
    }

    /**
     * Assert that a given request was sent.
     *
     * @param string|callable $value
     * @return void
     * @throws \ReflectionException
     */
    public static function assertSent(string|callable $value): void
    {
        static::mockClient()->assertSent($value);
    }

    /**
     * Assert that a given request was not sent.
     *
     * @param string|callable $value
     * @return void
     * @throws \ReflectionException
     */
    public static function assertNotSent(string|callable $value): void
    {
        static::mockClient()->assertNotSent($value);
    }

    /**
     * Assert JSON data was sent
     *
     * @param string $request
     * @param array $data
     * @return void
     * @throws \ReflectionException
     */
    public static function assertSentJson(string $request, array $data): void
    {
        static::mockClient()->assertSentJson($request, $data);
    }

    /**
     * Assert that nothing was sent.
     *
     * @return void
     */
    public static function assertNothingSent(): void
    {
        static::mockClient()->assertNothingSent();
    }

    /**
     * Assert a request count has been met.
     *
     * @param int $count
     * @return void
     */
    public static function assertSentCount(int $count): void
    {
        static::mockClient()->assertSentCount($count);
    }

    /**
     * Start Saloon recording responses.
     *
     * @return void
     */
    public function record(): void
    {
        $this->record = true;
    }

    /**
     * Stop Saloon recording responses.
     *
     * @return void
     */
    public function stopRecording(): void
    {
        $this->record = false;
    }

    /**
     * Check if Saloon is recording
     *
     * @return bool
     */
    public function isRecording(): bool
    {
        return $this->record;
    }

    /**
     * Record a response.
     *
     * @param Response $response
     * @return void
     */
    public function recordResponse(Response $response): void
    {
        $this->recordedResponses[] = $response;
    }

    /**
     * Get all the recorded responses.
     *
     * @return array
     */
    public function getRecordedResponses(): array
    {
        return $this->recordedResponses;
    }

    /**
     * Get the last response that Saloon recorded.
     *
     * @return Response|null
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
