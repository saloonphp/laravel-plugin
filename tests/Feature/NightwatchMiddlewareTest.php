<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Http\Middleware\NightwatchMiddleware;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

test('nightwatch middleware is invoked without errors when nightwatch is not available', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new NightwatchMiddleware();

    // Should not throw any exceptions even when Nightwatch is not available
    expect(function () use ($middleware, $pendingRequest) {
        $middleware($pendingRequest);
    })->not->toThrow(Exception::class);
});

test('nightwatch middleware checks for guzzle sender', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new NightwatchMiddleware();

    // Should handle gracefully for any sender type
    expect(function () use ($middleware, $pendingRequest) {
        $middleware($pendingRequest);
    })->not->toThrow(Exception::class);
});
