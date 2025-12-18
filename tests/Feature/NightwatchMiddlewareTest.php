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

    /*
     *    Handler stack __toString() renders middleware as in > and out <.
     *    Example:
     *    > 5) Name: 'http_errors', Function: callable(00000000000004130000000000000000)
     *   > 4) Name: 'allow_redirects', Function: callable(00000000000004120000000000000000)
     *   > 3) Name: 'cookies', Function: callable(00000000000004110000000000000000)
     *   > 2) Name: 'prepare_body', Function: callable(00000000000004100000000000000000)
     *   > 1) Name: 'nightwatch', Function: callable(00000000000004440000000000000000)
     *   < 0) Handler: callable(00000000000004170000000000000000)
     *   < 1) Name: 'nightwatch', Function: callable(00000000000004440000000000000000)
     *   < 2) Name: 'prepare_body', Function: callable(00000000000004100000000000000000)
     *   < 3) Name: 'cookies', Function: callable(00000000000004110000000000000000)
     *   < 4) Name: 'allow_redirects', Function: callable(00000000000004120000000000000000)
     *   < 5) Name: 'http_errors', Function: callable(00000000000004130000000000000000)
     *
     *   Count only the ">" section to avoid double counting
     */
    $stackString = (string) $handlerStack;
    $reverseSection = explode('<', $stackString)[0] ?? '';
    $nightwatchCount = mb_substr_count($reverseSection, 'Name: \'nightwatch\'');

    expect($nightwatchCount)->toBe(1);
});
