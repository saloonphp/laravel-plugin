<?php

declare(strict_types=1);

use Saloon\Laravel\Saloon;
use Saloon\Http\PendingRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Http\Middleware\TelescopeRequestMiddleware;
use Saloon\Laravel\Http\Middleware\TelescopeResponseMiddleware;

test('telescope middleware handles sending event without errors when telescope is not available', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new TelescopeRequestMiddleware();

    // Should not throw any exceptions even when Telescope is not available
    expect(function () use ($middleware, $pendingRequest) {
        $middleware->__invoke($pendingRequest);
    })->not->toThrow(Exception::class);
});

test('telescope middleware works with any sender', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new TelescopeRequestMiddleware();

    // Should handle gracefully for any sender type
    expect(function () use ($middleware, $pendingRequest) {
        $middleware->__invoke($pendingRequest);
    })->not->toThrow(Exception::class);
});

test('telescope middleware tracks start time', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();

    expect(Saloon::$telescopeStartTimes)->toHaveCount(0);

    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new TelescopeRequestMiddleware();
    $middleware->__invoke($pendingRequest);

    expect(Saloon::$telescopeStartTimes)->toHaveCount(1);
});

test('telescope middleware calculates duration', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    // Create a mock response
    $psrRequest = $pendingRequest->createPsrRequest();
    $psrResponse = new \GuzzleHttp\Psr7\Response(200, [], '{"name":"Test"}');
    $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

    $middleware = new TelescopeRequestMiddleware();

    // Handle sending event to set start time
    $middleware->__invoke($pendingRequest);

    // Small delay
    usleep(1000); // 1ms

    // Handle sent event
    expect(function () use ($response) {
        (new TelescopeResponseMiddleware)->__invoke($response);
    })->not->toThrow(Exception::class);
});

test('telescope middleware formats json body', function () {
    $middleware = new TelescopeResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');

    $jsonBody = '{"name":"Test","value":123}';
    $formatted = $method->invoke($middleware, $jsonBody, 'application/json');

    expect($formatted)->toBeArray();
    expect($formatted)->toHaveKeys(['name', 'value']);
    expect($formatted['name'])->toBe('Test');
    expect($formatted['value'])->toBe(123);
});

test('telescope middleware formats form body', function () {
    $middleware = new TelescopeResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');

    $formBody = 'name=Test&value=123';
    $formatted = $method->invoke($middleware, $formBody, 'application/x-www-form-urlencoded');

    expect($formatted)->toBeArray();
    expect($formatted)->toHaveKeys(['name', 'value']);
    expect($formatted['name'])->toBe('Test');
    expect($formatted['value'])->toBe('123');
});

test('telescope middleware returns string for non-json non-form body', function () {
    $middleware = new TelescopeResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');

    $plainBody = 'plain text body';
    $formatted = $method->invoke($middleware, $plainBody, 'text/plain');

    expect($formatted)->toBeString();
    expect($formatted)->toBe('plain text body');
});
