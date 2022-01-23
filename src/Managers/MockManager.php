<?php

namespace Sammyjo20\SaloonLaravel\Managers;

use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\SaloonRequest;

class MockManager
{
    /**
     * Is Saloon mocking?
     *
     * @var bool
     */
    protected bool $isMocking = false;

    /**
     * The sequence of tests
     *
     * @var array
     */
    protected array $sequence = [];

    /**
     * Start mocking
     *
     * @return $this
     */
    public function startMocking(array $responses = []): self
    {
        $this->isMocking = true;

        $this->storeResponses($responses);

        return $this;
    }

    /**
     * Store the mock responses in the correct places.
     *
     * @param array $responses
     * @return void
     */
    private function storeResponses(array $responses): void
    {
        if (empty($responses)) {
            throw new SaloonNoMockResponsesProvidedException;
        }

        // Sequence tests...

        if (array_is_list($responses)) {
            $this->sequence = $responses;
            return;
        }

        // Other tests, based on URL...

        // Test array keys for urls
        // Test array keys for request classes
        // Test array keys for response classes

        dd('Process other...', $responses);

        foreach ($responses as $key => $response) {
            dd($key, $response);
        }
    }

    /**
     * Check if we are mocking or not
     *
     * @return bool
     */
    public function isMocking(): bool
    {
        return $this->isMocking;
    }

    /**
     * @return mixed
     */
    public function getNextFromSequence(): mixed
    {
        return array_shift($this->sequence);
    }

    /**
     * Guess the next response based on the request.
     *
     * @param SaloonRequest $request
     * @return MockResponse
     */
    public function guessNextResponse(SaloonRequest $request): MockResponse
    {
        // Check if there is an explicit response for this request
        // Check if there is an explicit response for the request's connector
        // Check if there is a response for the url
        // Otherwise, use the sequence.

        return $this->getNextFromSequence();
    }

    /**
     * Return the SaloonMockManager out of the Laravel container
     *
     * @return \Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function resolve(): static
    {
        return resolve(static::class);
    }
}
