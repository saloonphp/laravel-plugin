<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Faking;

use Saloon\Http\Faking\MockClient as BaseMockClient;

class MockClient extends BaseMockClient
{
    /**
     * Denotes if the MockClient is mocking
     *
     * @var bool
     */
    protected bool $isMocking = false;

    /**
     * Start Mocking Responses
     *
     * @param array $responses
     * @return $this
     * @throws \Saloon\Exceptions\InvalidMockResponseCaptureMethodException
     */
    public function startMocking(array $responses = []): self
    {
        $this->addResponses($responses);

        $this->isMocking = true;

        return $this;
    }

    /**
     * Check if we are mocking
     *
     * @return bool
     */
    public function isMocking(): bool
    {
        return $this->isMocking;
    }

    /**
     * Resolve the MockClient from the container
     *
     * @return static
     */
    public static function resolve(): static
    {
        return resolve(static::class);
    }
}
