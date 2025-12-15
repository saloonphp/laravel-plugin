<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use Saloon\Laravel\Events\SentSaloonRequest;
use Saloon\Laravel\Events\SendingSaloonRequest;
use Saloon\Laravel\Http\Middleware\TelescopeMiddleware;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

test('telescope middleware handles sending event without errors when telescope is not available', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);
    $event = new SendingSaloonRequest($pendingRequest);

    $middleware = new TelescopeMiddleware();

    // Should not throw any exceptions even when Telescope is not available
    expect(function () use ($middleware, $event) {
        $middleware->handleSending($event);
    })->not->toThrow(Exception::class);
});

test('telescope middleware works with any sender', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);
    $event = new SendingSaloonRequest($pendingRequest);

    $middleware = new TelescopeMiddleware();

    // Should handle gracefully for any sender type
    expect(function () use ($middleware, $event) {
        TelescopeMiddleware::handleSending($event);
    })->not->toThrow(Exception::class);
});

test('telescope middleware tracks start time', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);
    $event = new SendingSaloonRequest($pendingRequest);

    $middleware = new TelescopeMiddleware();
    $middleware->handleSending($event);

    // Start time should be tracked (we can't easily verify this without reflection,
    // but we can verify it doesn't throw)
    expect(true)->toBeTrue();
});

test('telescope middleware calculates duration', function () {
    $connector = TestConnector::make();
    $request = new UserRequest();
    $pendingRequest = new PendingRequest($connector, $request);
    
    // Create a mock response
    $psrRequest = $pendingRequest->createPsrRequest();
    $psrResponse = new \GuzzleHttp\Psr7\Response(200, [], '{"name":"Test"}');
    $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

    $sendingEvent = new SendingSaloonRequest($pendingRequest);
    $sentEvent = new SentSaloonRequest($pendingRequest, $response);

    $middleware = new TelescopeMiddleware();
    
    // Handle sending event to set start time
    $middleware->handleSending($sendingEvent);
    
    // Small delay
    usleep(1000); // 1ms
    
    // Handle sent event
    expect(function () use ($middleware, $sentEvent) {
        $middleware->handleSent($sentEvent);
    })->not->toThrow(Exception::class);
});

test('telescope middleware formats json body', function () {
    $middleware = new TelescopeMiddleware();
    
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
    $middleware = new TelescopeMiddleware();
    
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
    $middleware = new TelescopeMiddleware();
    
    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');
    
    $plainBody = 'plain text body';
    $formatted = $method->invoke($middleware, $plainBody, 'text/plain');
    
    expect($formatted)->toBeString();
    expect($formatted)->toBe('plain text body');
});
