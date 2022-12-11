<?php declare(strict_types=1);

use Saloon\Http\Request;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Responses\Response;
use Saloon\Laravel\Facades\Saloon;
use GuzzleHttp\Exception\ConnectException;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\ErrorRequest;

test('that assertSent works with a request', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    TestConnector::make()->send(new UserRequest);

    Saloon::assertSent(UserRequest::class);
});

test('that assertSent works with a closure', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new UserRequest();
    $originalResponse = TestConnector::make()->send($originalRequest);

    Saloon::assertSent(function ($request, $response) use ($originalRequest, $originalResponse) {
        expect($request)->toBeInstanceOf(Request::class);
        expect($response)->toBeInstanceOf(Response::class);

        expect($request)->toBe($originalRequest);
        expect($response)->toBe($originalResponse);

        return true;
    });

    $newRequest = new ErrorRequest();
    $newResponse = TestConnector::make()->send($newRequest);

    Saloon::assertSent(function ($request, $response) use ($newRequest, $newResponse) {
        expect($request)->toBeInstanceOf(Request::class);
        expect($response)->toBeInstanceOf(Response::class);

        expect($request)->toBe($newRequest);
        expect($response)->toBe($newResponse);

        return true;
    });
});

test('that assertSent works with a url', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    TestConnector::make()->send(new UserRequest);

    Saloon::assertSent('saloon.dev/*');
    Saloon::assertSent('/user');
    Saloon::assertSent('api/user');
});

test('that assertNotSent works with a request', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    TestConnector::make()->send(new ErrorRequest);

    Saloon::assertNotSent(UserRequest::class);
});

test('that assertNotSent works with a closure', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new ErrorRequest();
    $originalResponse = TestConnector::make()->send($originalRequest);

    Saloon::assertNotSent(function ($request) {
        return $request instanceof UserRequest;
    });
});

test('that assertNotSent works with a url', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    TestConnector::make()->send(new UserRequest);

    Saloon::assertNotSent('google.com/*');
    Saloon::assertNotSent('/error');
});

test('that assertSentJson works properly', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    TestConnector::make()->send(new UserRequest);

    Saloon::assertSentJson(UserRequest::class, [
        'name' => 'Sam',
    ]);
});

test('test assertNothingSent works properly', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    Saloon::assertNothingSent();
});

test('test assertSentCount works properly', function () {
    Saloon::fake([
        new MockResponse(['name' => 'Sam'], 200),
        new MockResponse(['name' => 'Taylor'], 200),
        new MockResponse(['name' => 'Marcel'], 200),
    ]);

    TestConnector::make()->send(new UserRequest);
    TestConnector::make()->send(new UserRequest);
    TestConnector::make()->send(new UserRequest);

    Saloon::assertSentCount(3);
});

test('you can mock exceptions', function () {
    Saloon::fake([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Patrick'])->throw(fn ($pendingRequest) => new Exception('Unable to connect!')),
    ]);

    $okResponse = TestConnector::make()->send(new UserRequest);

    expect($okResponse->json())->toEqual(['name' => 'Sam']);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Custom Exception!');

    TestConnector::make()->send(new UserRequest);
});

test('you can mock normal exceptions', function () {
    Saloon::fake([
        MockResponse::make(['name' => 'Michael'])->throw(new Exception('Custom Exception!')),
    ]);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Custom Exception!');

    TestConnector::make()->send(new UserRequest);
});
