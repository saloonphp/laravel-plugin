<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Sammyjo20\SaloonLaravel\Tests\Resources\Requests\UserRequest;
use Sammyjo20\SaloonLaravel\Tests\Resources\Requests\ErrorRequest;

test('that assertSent works with a request', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send();

    Saloon::assertSent(UserRequest::class);
});

test('that assertSent works with a closure', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new UserRequest();
    $originalResponse = $originalRequest->send();

    Saloon::assertSent(function ($request, $response) use ($originalRequest, $originalResponse) {
        expect($request)->toBeInstanceOf(SaloonRequest::class);
        expect($response)->toBeInstanceOf(SaloonResponse::class);

        expect($request)->toBe($originalRequest);
        expect($response)->toBe($originalResponse);

        return true;
    });

    $newRequest = new ErrorRequest();
    $newResponse = $newRequest->send();

    Saloon::assertSent(function ($request, $response) use ($newRequest, $newResponse) {
        expect($request)->toBeInstanceOf(SaloonRequest::class);
        expect($response)->toBeInstanceOf(SaloonResponse::class);

        expect($request)->toBe($newRequest);
        expect($response)->toBe($newResponse);

        return true;
    });
});

test('that assertSent works with a url', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send();

    Saloon::assertSent('saloon.dev/*');
    Saloon::assertSent('/user');
    Saloon::assertSent('api/user');
});

test('that assertNotSent works with a request', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    (new ErrorRequest())->send();

    Saloon::assertNotSent(UserRequest::class);
});

test('that assertNotSent works with a closure', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new ErrorRequest();
    $originalResponse = $originalRequest->send();

    Saloon::assertNotSent(function ($request) {
        return $request instanceof UserRequest;
    });
});

test('that assertNotSent works with a url', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send();

    Saloon::assertNotSent('google.com/*');
    Saloon::assertNotSent('/error');
});

test('that assertSentJson works properly', function () {
    Saloon::fake([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send();

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

    (new UserRequest())->send();
    (new UserRequest())->send();
    (new UserRequest())->send();

    Saloon::assertSentCount(3);
});
