<?php

declare(strict_types=1);

use Saloon\Laravel\Saloon;
use Saloon\Http\PendingRequest;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\Telescope;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Http\Middleware\TelescopeRequestMiddleware;
use Saloon\Laravel\Http\Middleware\TelescopeResponseMiddleware;
use Saloon\Laravel\Tests\Fixtures\Requests\PostSensitiveFormRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\PostSensitiveJsonRequest;

test('telescope middleware handles sending event without errors when telescope is not available', function () {
    $connector = TestConnector::make();
    $request = new UserRequest;
    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new TelescopeRequestMiddleware;

    // Should not throw any exceptions even when Telescope is not available
    expect(function () use ($middleware, $pendingRequest) {
        $middleware->__invoke($pendingRequest);
    })->not->toThrow(Exception::class);
});

test('telescope middleware works with any sender', function () {
    $connector = TestConnector::make();
    $request = new UserRequest;
    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new TelescopeRequestMiddleware;

    // Should handle gracefully for any sender type
    expect(function () use ($middleware, $pendingRequest) {
        $middleware->__invoke($pendingRequest);
    })->not->toThrow(Exception::class);
});

test('telescope middleware tracks start time', function () {
    $connector = TestConnector::make();
    $request = new UserRequest;

    expect(Saloon::$telescopeStartTimes)->toHaveCount(0);

    $pendingRequest = new PendingRequest($connector, $request);

    $middleware = new TelescopeRequestMiddleware;
    $middleware->__invoke($pendingRequest);

    expect(Saloon::$telescopeStartTimes)->toHaveCount(1);
});

test('telescope middleware calculates duration', function () {
    $connector = TestConnector::make();
    $request = new UserRequest;
    $pendingRequest = new PendingRequest($connector, $request);

    // Create a mock response
    $psrRequest = $pendingRequest->createPsrRequest();
    $psrResponse = new \GuzzleHttp\Psr7\Response(200, [], '{"name":"Test"}');
    $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

    $middleware = new TelescopeRequestMiddleware;

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
    $middleware = new TelescopeResponseMiddleware;

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
    $middleware = new TelescopeResponseMiddleware;

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
    $middleware = new TelescopeResponseMiddleware;

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');

    $plainBody = 'plain text body';
    $formatted = $method->invoke($middleware, $plainBody, 'text/plain');

    expect($formatted)->toBeString();
    expect($formatted)->toBe('plain text body');
});

test('telescope middleware returns string for non-json body when header says response is json', function () {
    $middleware = new TelescopeResponseMiddleware;

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');

    $plainBody = 123456;
    $formatted = $method->invoke($middleware, $plainBody, 'application/json');

    expect($formatted)->toBeString();
    expect($formatted)->toBe('123456');
});

test('telescope middleware formats hal+json body', function () {
    $middleware = new TelescopeResponseMiddleware;

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');

    $jsonBody = '{"_links":{"self":{"href":"/api/users/1"}},"name":"Test"}';
    $formatted = $method->invoke($middleware, $jsonBody, 'application/hal+json');

    expect($formatted)->toBeArray();
    expect($formatted)->toHaveKeys(['_links', 'name']);
    expect($formatted['name'])->toBe('Test');
});

test('telescope middleware formats hal+json body with charset', function () {
    $middleware = new TelescopeResponseMiddleware;

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');

    $jsonBody = '{"id":1}';
    $formatted = $method->invoke($middleware, $jsonBody, 'application/hal+json; charset=utf-8');

    expect($formatted)->toBeArray();
    expect($formatted['id'])->toBe(1);
});

test('telescope middleware formats json api body', function () {
    $middleware = new TelescopeResponseMiddleware;

    $reflection = new ReflectionClass($middleware);
    $method = $reflection->getMethod('formatBody');

    $jsonBody = '{"data":{"type":"users","id":"1"}}';
    $formatted = $method->invoke($middleware, $jsonBody, 'application/vnd.api+json');

    expect($formatted)->toBeArray();
    expect($formatted['data']['type'])->toBe('users');
});

/**
 * @return array{hiddenRequestParameters: array<int, string>, hiddenRequestHeaders: array<int, string>, hiddenResponseParameters: array<int, string>, shouldRecord: bool}
 */
function snapshotTelescopeRedactionSettings(): array
{
    return [
        'hiddenRequestParameters' => Telescope::$hiddenRequestParameters,
        'hiddenRequestHeaders' => Telescope::$hiddenRequestHeaders,
        'hiddenResponseParameters' => Telescope::$hiddenResponseParameters,
        'shouldRecord' => Telescope::$shouldRecord,
    ];
}

/**
 * @param  array{hiddenRequestParameters: array<int, string>, hiddenRequestHeaders: array<int, string>, hiddenResponseParameters: array<int, string>, shouldRecord: bool}  $snapshot
 */
function restoreTelescopeRedactionSettings(array $snapshot): void
{
    Telescope::$hiddenRequestParameters = $snapshot['hiddenRequestParameters'];
    Telescope::$hiddenRequestHeaders = $snapshot['hiddenRequestHeaders'];
    Telescope::$hiddenResponseParameters = $snapshot['hiddenResponseParameters'];
    Telescope::$shouldRecord = $snapshot['shouldRecord'];
    Telescope::flushEntries();
}

test('telescope response middleware redacts request payload and authorization header for recorded client requests', function () {
    $snapshot = snapshotTelescopeRedactionSettings();

    try {
        Telescope::flushEntries();
        Telescope::$shouldRecord = true;
        Telescope::$hiddenRequestParameters = ['password', 'client_secret'];
        Telescope::$hiddenRequestHeaders = ['authorization'];
        Telescope::$hiddenResponseParameters = [];

        $connector = TestConnector::make();
        $connector->headers()->add('Authorization', 'Bearer bearer-token-plaintext');
        $request = new PostSensitiveJsonRequest;
        $pendingRequest = new PendingRequest($connector, $request);

        (new TelescopeRequestMiddleware)->__invoke($pendingRequest);

        $psrRequest = $pendingRequest->createPsrRequest();
        $psrResponse = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"ok":true}');
        $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

        (new TelescopeResponseMiddleware)->__invoke($response);

        expect(Telescope::$entriesQueue)->not->toBeEmpty();

        $entry = collect(Telescope::$entriesQueue)->last(fn ($e) => $e->type === EntryType::CLIENT_REQUEST);

        expect($entry)->not->toBeNull();
        expect($entry->content['payload']['password'])->toBe('********');
        expect($entry->content['payload']['client_secret'])->toBe('********');
        expect($entry->content['payload']['email'])->toBe('user@example.com');
        expect($entry->content['headers']['authorization'])->toBe('********');
    } finally {
        restoreTelescopeRedactionSettings($snapshot);
    }
});

test('telescope response middleware redacts JSON response fields using hidden response parameters', function () {
    $snapshot = snapshotTelescopeRedactionSettings();

    try {
        Telescope::flushEntries();
        Telescope::$shouldRecord = true;
        Telescope::$hiddenRequestParameters = [];
        Telescope::$hiddenRequestHeaders = [];
        Telescope::$hiddenResponseParameters = ['access_token'];

        $connector = TestConnector::make();
        $request = new PostSensitiveJsonRequest;
        $pendingRequest = new PendingRequest($connector, $request);

        (new TelescopeRequestMiddleware)->__invoke($pendingRequest);

        $psrRequest = $pendingRequest->createPsrRequest();
        $responseBody = json_encode(['access_token' => 'secret-token-value', 'expires_in' => 3600]);
        $psrResponse = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], $responseBody);
        $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

        (new TelescopeResponseMiddleware)->__invoke($response);

        $entry = collect(Telescope::$entriesQueue)->last(fn ($e) => $e->type === EntryType::CLIENT_REQUEST);

        expect($entry)->not->toBeNull();
        expect($entry->content['response']['access_token'])->toBe('********');
        expect($entry->content['response']['expires_in'])->toBe(3600);
    } finally {
        restoreTelescopeRedactionSettings($snapshot);
    }
});

test('telescope response middleware redacts application/x-www-form-urlencoded payload', function () {
    $snapshot = snapshotTelescopeRedactionSettings();

    try {
        Telescope::flushEntries();
        Telescope::$shouldRecord = true;
        Telescope::$hiddenRequestParameters = ['password'];
        Telescope::$hiddenRequestHeaders = [];
        Telescope::$hiddenResponseParameters = [];

        $connector = TestConnector::make();
        $request = new PostSensitiveFormRequest;
        $pendingRequest = new PendingRequest($connector, $request);

        (new TelescopeRequestMiddleware)->__invoke($pendingRequest);

        $psrRequest = $pendingRequest->createPsrRequest();
        $psrResponse = new \GuzzleHttp\Psr7\Response(200, [], '');
        $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

        (new TelescopeResponseMiddleware)->__invoke($response);

        $entry = collect(Telescope::$entriesQueue)->last(fn ($e) => $e->type === EntryType::CLIENT_REQUEST);

        expect($entry)->not->toBeNull();
        expect($entry->content['payload']['password'])->toBe('********');
        expect($entry->content['payload']['email'])->toBe('a@b.test');
        expect($entry->content['payload']['name'])->toBe('test');
    } finally {
        restoreTelescopeRedactionSettings($snapshot);
    }
});

test('telescope response middleware records hal+json response as array', function () {
    $snapshot = snapshotTelescopeRedactionSettings();

    try {
        Telescope::flushEntries();
        Telescope::$shouldRecord = true;
        Telescope::$hiddenRequestParameters = [];
        Telescope::$hiddenRequestHeaders = [];
        Telescope::$hiddenResponseParameters = [];

        $connector = TestConnector::make();
        $request = new PostSensitiveJsonRequest;
        $pendingRequest = new PendingRequest($connector, $request);

        (new TelescopeRequestMiddleware)->__invoke($pendingRequest);

        $psrRequest = $pendingRequest->createPsrRequest();
        $responseBody = json_encode(['_links' => ['self' => ['href' => '/api/users/1']], 'name' => 'Test']);
        $psrResponse = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/hal+json'], $responseBody);
        $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

        (new TelescopeResponseMiddleware)->__invoke($response);

        $entry = collect(Telescope::$entriesQueue)->last(fn ($e) => $e->type === EntryType::CLIENT_REQUEST);

        expect($entry)->not->toBeNull();
        expect($entry->content['response'])->toBeArray();
        expect($entry->content['response'])->not->toBe('HTML Response');
        expect($entry->content['response']['name'])->toBe('Test');
    } finally {
        restoreTelescopeRedactionSettings($snapshot);
    }
});

test('telescope response middleware redacts hal+json response fields using hidden response parameters', function () {
    $snapshot = snapshotTelescopeRedactionSettings();

    try {
        Telescope::flushEntries();
        Telescope::$shouldRecord = true;
        Telescope::$hiddenRequestParameters = [];
        Telescope::$hiddenRequestHeaders = [];
        Telescope::$hiddenResponseParameters = ['access_token'];

        $connector = TestConnector::make();
        $request = new PostSensitiveJsonRequest;
        $pendingRequest = new PendingRequest($connector, $request);

        (new TelescopeRequestMiddleware)->__invoke($pendingRequest);

        $psrRequest = $pendingRequest->createPsrRequest();
        $responseBody = json_encode(['access_token' => 'secret-token-value', 'expires_in' => 3600]);
        $psrResponse = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/hal+json'], $responseBody);
        $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

        (new TelescopeResponseMiddleware)->__invoke($response);

        $entry = collect(Telescope::$entriesQueue)->last(fn ($e) => $e->type === EntryType::CLIENT_REQUEST);

        expect($entry)->not->toBeNull();
        expect($entry->content['response']['access_token'])->toBe('********');
        expect($entry->content['response']['expires_in'])->toBe(3600);
    } finally {
        restoreTelescopeRedactionSettings($snapshot);
    }
});

test('telescope response middleware records html response as html response label', function () {
    $snapshot = snapshotTelescopeRedactionSettings();

    try {
        Telescope::flushEntries();
        Telescope::$shouldRecord = true;
        Telescope::$hiddenRequestParameters = [];
        Telescope::$hiddenRequestHeaders = [];
        Telescope::$hiddenResponseParameters = [];

        $connector = TestConnector::make();
        $request = new PostSensitiveJsonRequest;
        $pendingRequest = new PendingRequest($connector, $request);

        (new TelescopeRequestMiddleware)->__invoke($pendingRequest);

        $psrRequest = $pendingRequest->createPsrRequest();
        $psrResponse = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/html'], '<html><body>Hello</body></html>');
        $response = \Saloon\Http\Response::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest);

        (new TelescopeResponseMiddleware)->__invoke($response);

        $entry = collect(Telescope::$entriesQueue)->last(fn ($e) => $e->type === EntryType::CLIENT_REQUEST);

        expect($entry)->not->toBeNull();
        expect($entry->content['response'])->toBe('HTML Response');
    } finally {
        restoreTelescopeRedactionSettings($snapshot);
    }
});
