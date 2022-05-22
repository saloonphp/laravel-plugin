<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Connectors\QueryParameterConnector;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\QueryParameterConnectorRequest;

test('a request can be mocked with a sequence', function () {
    Saloon::fake([
        new MockResponse(['name' => 'Sam'], 200),
        new MockResponse(['name' => 'Alex'], 200),
        new MockResponse(['error' => 'Server Unavailable'], 500),
    ]);

    $responseA = (new UserRequest)->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->status())->toEqual(200);

    $responseB = (new UserRequest)->send();

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = (new UserRequest)->send();

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Unavailable']);
    expect($responseC->status())->toEqual(500);

    $this->expectException(SaloonNoMockResponsesProvidedException::class);

    (new UserRequest)->send();
});

test('a request can be mocked with a connector defined', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);

    $connectorARequest = new UserRequest;
    $connectorBRequest = new QueryParameterConnectorRequest;

    Saloon::fake([
        TestConnector::class => $responseA,
        QueryParameterConnector::class => $responseB,
    ]);

    $responseA = $connectorARequest->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $connectorBRequest->send();

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);
});

test('a request can be mocked with a request defined', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);

    $requestA = new UserRequest;
    $requestB = new QueryParameterConnectorRequest;

    Saloon::fake([
        UserRequest::class => $responseA,
        QueryParameterConnectorRequest::class => $responseB,
    ]);

    $responseA = $requestA->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $requestB->send();

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);
});

test('a request can be mocked with a url defined', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);
    $responseC = new MockResponse(['error' => 'Server Broken'], 500);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    Saloon::fake([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        'google.com/*' => $responseC, // Test Different Route,
    ]);

    $responseA = $requestA->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $requestB->send();

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = $requestC->send();

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Broken']);
    expect($responseC->status())->toEqual(500);
});

test('you can create wildcard url mocks', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);
    $responseC = new MockResponse(['error' => 'Server Broken'], 500);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    Saloon::fake([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        '*' => $responseC,
    ]);

    $responseA = $requestA->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $requestB->send();

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = $requestC->send();

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Broken']);
    expect($responseC->status())->toEqual(500);
});
