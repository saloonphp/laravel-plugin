<?php

declare(strict_types=1);

use Saloon\Laravel\Saloon;
use Saloon\Http\PendingRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Http\Middleware\PulseRequestMiddleware;
use Saloon\Laravel\Http\Middleware\PulseResponseMiddleware;

test('pulse middleware handles sending event without errors when pulse is not available', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new PulseRequestMiddleware();

    // Should not throw any exceptions even when Pulse is not available
    expect(function () use ($middleware, $pendingRequest) {
        $middleware->__invoke($pendingRequest);
    })->not->toThrow(Exception::class);
});

test('pulse middleware works with any sender', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new PulseRequestMiddleware();

    // Should handle gracefully for any sender type
    expect(function () use ($middleware, $pendingRequest) {
        $middleware->__invoke($pendingRequest);
    })->not->toThrow(Exception::class);
});

test('pulse middleware tracks start time', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();

    expect(Saloon::$pulseStartTimes)->toHaveCount(0);

    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new PulseRequestMiddleware();
    $middleware->__invoke($pendingRequest);

    expect(Saloon::$pulseStartTimes)->toHaveCount(1);
});

test('pulse middleware calculates duration', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    // Create a mock response
    $psrRequest = $pendingRequest->createPsrRequest();
    $psrResponse = new \GuzzleHttp\Psr7\Response(200, [], '{"name":"Test"}');
    $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

    $middleware = new PulseRequestMiddleware();

    // Handle sending event to set start time
    $middleware->__invoke($pendingRequest);

    // Small delay
    usleep(1000); // 1ms

    // Handle sent event
    expect(function () use ($response) {
        (new PulseResponseMiddleware)->__invoke($response);
    })->not->toThrow(Exception::class);
});

test('pulse middleware handles response when pulse is not available', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);

    // Create a mock response
    $psrRequest = $pendingRequest->createPsrRequest();
    $psrResponse = new \GuzzleHttp\Psr7\Response(200, [], '{"name":"Test"}');
    $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

    $middleware = new PulseResponseMiddleware();

    // Should not throw any exceptions even when Pulse is not available
    expect(function () use ($middleware, $response) {
        $middleware->__invoke($response);
    })->not->toThrow(Exception::class);
});

test('pulse middleware gets threshold for simple integer threshold', function () {
    $middleware = new PulseResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('getThreshold');

    $uri = 'https://example.com/api/users';
    $threshold = 100;
    $result = $method->invoke($middleware, $uri, $threshold);

    expect($result)->toBe(100);
});

test('pulse middleware gets threshold for array with pattern match', function () {
    $middleware = new PulseResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('getThreshold');

    $uri = 'https://example.com/api/users/123';
    $threshold = [
        '/api\/users\/\d+/' => 200,
        'default' => 100,
    ];
    $result = $method->invoke($middleware, $uri, $threshold);

    expect($result)->toBe(200);
});

test('pulse middleware gets threshold for array with default fallback', function () {
    $middleware = new PulseResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('getThreshold');

    $uri = 'https://example.com/api/posts';
    $threshold = [
        '/api\/users\/\d+/' => 200,
        'default' => 150,
    ];
    $result = $method->invoke($middleware, $uri, $threshold);

    expect($result)->toBe(150);
});

test('pulse middleware groups uri with pattern match', function () {
    $middleware = new PulseResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('groupUri');

    $uri = 'https://example.com/api/users/123';
    $groups = [
        '/\/users\/\d+/' => '/users/{id}',
    ];
    $result = $method->invoke($middleware, $uri, $groups);

    expect($result)->toBe('https://example.com/api/users/{id}');
});

test('pulse middleware groups uri without match returns original', function () {
    $middleware = new PulseResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('groupUri');

    $uri = 'https://example.com/api/posts';
    $groups = [
        '/\/users\/\d+/' => '/users/{id}',
    ];
    $result = $method->invoke($middleware, $uri, $groups);

    expect($result)->toBe('https://example.com/api/posts');
});

test('pulse middleware groups uri with empty groups returns original', function () {
    $middleware = new PulseResponseMiddleware();

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('groupUri');

    $uri = 'https://example.com/api/users/123';
    $groups = [];
    $result = $method->invoke($middleware, $uri, $groups);

    expect($result)->toBe('https://example.com/api/users/123');
});

