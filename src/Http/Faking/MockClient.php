<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Faking;

use Saloon\Http\Faking\MockClient as BaseMockClient;

/**
 * @deprecated You should use MockClient::global() instead. This class will be removed in Saloon v4.
 */
class MockClient extends BaseMockClient
{
    /**
     * Denotes if the MockClient is mocking
     */
    protected bool $isMocking = false;

    /**
     * Start Mocking Responses
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $responses
     * @return $this
     */
    public function startMocking(array $responses = []): self
    {
        $this->addResponses($responses);

        $this->isMocking = true;

        return $this;
    }

    /**
     * Check if we are mocking
     */
    public function isMocking(): bool
    {
        return $this->isMocking;
    }

    /**
     * Resolve the MockClient from the container
     */
    public static function resolve(): static
    {
        return resolve(static::class);
    }
}
