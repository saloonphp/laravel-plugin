<?php

declare(strict_types=1);

use Saloon\Laravel\Facades\Saloon;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Tests\Fixtures\Connectors\QueryParameterConnector;
use Saloon\Laravel\Tests\Fixtures\Connectors\DifferentServiceConnector;
use Saloon\Laravel\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\QueryParameterConnectorRequest;

test('a request can be mocked with a sequence', function () {
    Saloon::fake([
        new MockResponse(['name' => 'Sam'], 200),
        new MockResponse(['name' => 'Alex'], 200),
        new MockResponse(['error' => 'Server Unavailable'], 500),
    ]);

    $connector = new TestConnector;

    $responseA = $connector->send(new UserRequest);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->status())->toEqual(200);

    $responseB = $connector->send(new UserRequest);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = $connector->send(new UserRequest);

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Unavailable']);
    expect($responseC->status())->toEqual(500);

    $this->expectException(NoMockResponseFoundException::class);

    $connector->send(new UserRequest);
});

test('a request can be mocked with a sequence using a closure', function () {
    Saloon::fake([
        function (PendingRequest $request): MockResponse {
            return new MockResponse(['request' => $request->getUrl()]);
        },
    ]);

    $responseA = TestConnector::make()->send(new UserRequest);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
    expect($responseA->status())->toEqual(200);
});

test('a request can be mocked with a connector defined', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20']);
    $responseB = new MockResponse(['name' => 'Alex']);

    $connectorA = new TestConnector;
    $connectorB = new QueryParameterConnector;

    $connectorARequest = new UserRequest;
    $connectorBRequest = new QueryParameterConnectorRequest;

    Saloon::fake([
        TestConnector::class => $responseA,
        QueryParameterConnector::class => $responseB,
    ]);

    $responseA = $connectorA->send($connectorARequest);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $connectorB->send($connectorBRequest);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);
});

test('a request can be mocked with a connector defined using a closure', function () {
    Saloon::fake([
        TestConnector::class => function (PendingRequest $request): MockResponse {
            return new MockResponse(['request' => $request->getUrl()]);
        },
    ]);

    $responseA = TestConnector::make()->send(new UserRequest);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
    expect($responseA->status())->toEqual(200);
});

test('a request can be mocked with a request defined', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20']);
    $responseB = new MockResponse(['name' => 'Alex']);

    $connectorA = new TestConnector;
    $connectorB = new QueryParameterConnector;

    $requestA = new UserRequest;
    $requestB = new QueryParameterConnectorRequest;

    Saloon::fake([
        UserRequest::class => $responseA,
        QueryParameterConnectorRequest::class => $responseB,
    ]);

    $responseA = $connectorA->send($requestA);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $connectorB->send($requestB);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);
});

test('a request can be mocked with a request defined using a closure', function () {
    Saloon::fake([
        UserRequest::class => function (PendingRequest $request): MockResponse {
            return new MockResponse(['request' => $request->getUrl()]);
        },
    ]);

    $responseA = TestConnector::make()->send(new UserRequest);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
    expect($responseA->status())->toEqual(200);
});

test('a request can be mocked with a url defined', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20']);
    $responseB = new MockResponse(['name' => 'Alex']);
    $responseC = new MockResponse(['error' => 'Server Broken'], 500);

    $connectorA = new TestConnector;
    $connectorB = new DifferentServiceConnector;

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    Saloon::fake([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        'google.com/*' => $responseC, // Test Different Route,
    ]);

    $responseA = $connectorA->send($requestA);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $connectorA->send($requestB);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = $connectorB->send($requestC);

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Broken']);
    expect($responseC->status())->toEqual(500);
});

test('you can create wildcard url mocks', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20']);
    $responseB = new MockResponse(['name' => 'Alex']);
    $responseC = new MockResponse(['error' => 'Server Broken'], 500);

    $connectorA = new TestConnector;
    $connectorB = new DifferentServiceConnector;

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    Saloon::fake([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        '*' => $responseC,
    ]);

    $responseA = $connectorA->send($requestA);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $connectorA->send($requestB);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = $connectorB->send($requestC);

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Broken']);
    expect($responseC->status())->toEqual(500);
});

test('a request can be mocked with a url defined using a closure', function () {
    Saloon::fake([
        'tests.saloon.dev/*' => function (PendingRequest $request): MockResponse {
            return new MockResponse(['request' => $request->getUrl()]);
        },
    ]);

    $responseA = TestConnector::make()->send(new UserRequest);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
    expect($responseA->status())->toEqual(200);
});
