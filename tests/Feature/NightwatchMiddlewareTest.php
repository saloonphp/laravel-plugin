<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
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

test('nightwatch middleware is only registered once on the handler stack for long-lived connectors', function () {
    if (! class_exists('Laravel\Nightwatch\Facades\Nightwatch')) {
        require_once __DIR__ . '/../Fixtures/Middleware/NightwatchMock.php';
    }

    $connector = TestConnector::make();
    $pendingRequest = new PendingRequest($connector, new UserRequest());

    $sender = $connector->sender();
    $this->assertInstanceOf(GuzzleSender::class, $sender);

    $handlerStack = $sender->getHandlerStack();

    $middleware = new NightwatchMiddleware();

    // Simulate multiple requests being sent
    $middleware($pendingRequest);
    $middleware($pendingRequest);

    $stackProperty = (new ReflectionClass($handlerStack))->getProperty('stack');
    $stack = $stackProperty->getValue($handlerStack);

    $nightwatchCount = count(array_filter(
        $stack,
        static fn (array $entry): bool => ($entry[1] ?? null) === 'nightwatch'
    ));

    expect($nightwatchCount)->toBe(1);
});
