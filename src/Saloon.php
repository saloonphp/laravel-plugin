<?php

namespace Sammyjo20\SaloonLaravel;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Managers\LaravelManager;
use Sammyjo20\SaloonLaravel\Clients\MockClient;
use Sammyjo20\SaloonLaravel\Managers\FeatureManager;

class Saloon
{
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
     * The boot method. This is called by Saloon and from within here, we can push almost anything
     * into Saloon, but most important - we can push interceptors and handlers 🚀
     *
     * @param LaravelManager $laravelManager
     * @param SaloonRequest $request
     * @return LaravelManager
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException
     */
    public static function bootLaravelFeatures(LaravelManager $laravelManager, SaloonRequest $request): LaravelManager
    {
        $manager = new FeatureManager($laravelManager, $request);

        $manager->bootMockingFeature()
            ->bootRecordingFeature()
            ->bootEventTriggers();

        return $manager->getLaravelManager();
    }

    /**
     * Start mocking!
     *
     * @param array $responses
     * @return MockClient
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidMockResponseCaptureMethodException
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
     * @param SaloonResponse $response
     * @return void
     */
    public function recordResponse(SaloonResponse $response): void
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
     * @return SaloonResponse|null
     */
    public function getLastRecordedResponse(): ?SaloonResponse
    {
        if (empty($this->recordedResponses)) {
            return null;
        }

        $lastResponse = end($this->recordedResponses);

        reset($this->recordedResponses);

        return $lastResponse;
    }
}
