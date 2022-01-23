<?php

use Sammyjo20\SaloonLaravel\Saloon;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\SaloonLaravel\Tests\Resources\Requests\UserRequest;
use Sammyjo20\Saloon\Exceptions\SaloonMultipleMockMethodsException;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;

test('it can detect laravel', function () {
    $requestManager = new RequestManager(new UserRequest());

    expect($requestManager->inLaravelEnvironment)->toBeTrue();
});

test('it can boot properly when using laravel mock', function () {
    Saloon::fake([
        new MockResponse([], 200),
    ]);

    $requestManager = new RequestManager(new UserRequest());

    expect($requestManager->isMocking())->toBeTrue();
});

test('it can boot properly when not using laravel mock', function () {
    $requestManager = new RequestManager(new UserRequest());

    expect($requestManager->isMocking())->toBeFalse();
});

test('it throws an exception when you try to mock without providing a response', function () {
    $this->expectException(SaloonNoMockResponsesProvidedException::class);

    Saloon::fake([]);

    (new UserRequest())->send();
});

test('it throws an exception when you try to use both mock methods', function () {
    Saloon::fake([
        new MockResponse([], 200),
    ]);

    $this->expectException(SaloonMultipleMockMethodsException::class);

    $mockClient = new MockClient([
        new MockResponse([], 200),
    ]);

    new RequestManager(new UserRequest(), $mockClient);
});
