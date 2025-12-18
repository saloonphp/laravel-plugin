<?php

declare(strict_types=1);

namespace Laravel\Nightwatch\Facades;

/**
 * Mock Nightwatch facade for testing when Nightwatch is not installed.
 * This allows us to test the handler stack manipulation with a real GuzzleSender.
 * Note: our namespace needs to match the Laravel Nightwatch facade
 */
class Nightwatch
{
    public static function guzzleMiddleware(): callable
    {
        return function (callable $handler): callable {
            return function ($request, $options) use ($handler) {
                return $handler($request, $options);
            };
        };
    }
}
