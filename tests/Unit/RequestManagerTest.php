<?php

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Constants\MockStrategies;
use Sammyjo20\Saloon\Exceptions\SaloonMultipleMockMethodsException;
use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;
use Sammyjo20\SaloonLaravel\Saloon;

test('it can detect laravel', function () {
    $requestManager = new RequestManager(new UserRequest());

    expect($requestManager->inLaravelEnvironment)->toBeTrue();
});

test('it can boot properly when using laravel mock', function () {
    Saloon::fake();

    $requestManager = new RequestManager(new UserRequest());

    expect($requestManager->isMocking())->toBeTrue();
    expect($requestManager->getMockStrategy())->toEqual(MockStrategies::LARAVEL);
});

test('it throws an exception when you try to use both mock methods', function () {
    Saloon::fake();

    $this->expectException(SaloonMultipleMockMethodsException::class);

    new RequestManager(new UserRequest(), new MockClient());
});
