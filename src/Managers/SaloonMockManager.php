<?php

namespace Sammyjo20\SaloonLaravel\Managers;

class SaloonMockManager
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
    public function startMocking(array $sequence = []): self
    {
        $this->isMocking = true;

        $this->sequence = $sequence;

        return $this;
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

    public function pullFromSequence(): mixed
    {
        return array_shift($this->sequence);
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
