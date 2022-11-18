<?php declare(strict_types=1);

use Saloon\Laravel\Facades\Saloon;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\NoMockResponsesProvidedException;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Tests\Fixtures\Connectors\QueryParameterConnector;
use Saloon\Laravel\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\QueryParameterConnectorRequest;

test('a request can be mocked with a sequence', function () {
    Saloon::fake([
        new MockResponse(200, ['name' => 'Sam']),
        new MockResponse(200, ['name' => 'Alex']),
        new MockResponse(500, ['error' => 'Server Unavailable']),
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

    $this->expectException(NoMockResponsesProvidedException::class);

    (new UserRequest)->send();
});

test('a request can be mocked with a sequence using a closure', function () {
    Saloon::fake([
        function (PendingRequest $request): MockResponse {
            return new MockResponse(200, ['request' => $request->getUrl()]);
        },
    ]);

    $responseA = (new UserRequest)->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
    expect($responseA->status())->toEqual(200);
});

test('a request can be mocked with a connector defined', function () {
    $responseA = new MockResponse(200, ['name' => 'Sammyjo20']);
    $responseB = new MockResponse(200, ['name' => 'Alex']);

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

test('a request can be mocked with a connector defined using a closure', function () {
    Saloon::fake([
        TestConnector::class => function (PendingRequest $request): MockResponse {
            return new MockResponse(200, ['request' => $request->getUrl()]);
        },
    ]);

    $responseA = (new UserRequest)->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
    expect($responseA->status())->toEqual(200);
});

test('a request can be mocked with a request defined', function () {
    $responseA = new MockResponse(200, ['name' => 'Sammyjo20']);
    $responseB = new MockResponse(200, ['name' => 'Alex']);

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

test('a request can be mocked with a request defined using a closure', function () {
    Saloon::fake([
        UserRequest::class => function (PendingRequest $request): MockResponse {
            return new MockResponse(200, ['request' => $request->getUrl()]);
        },
    ]);

    $responseA = (new UserRequest)->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
    expect($responseA->status())->toEqual(200);
});

test('a request can be mocked with a url defined', function () {
    $responseA = new MockResponse(200, ['name' => 'Sammyjo20']);
    $responseB = new MockResponse(200, ['name' => 'Alex']);
    $responseC = new MockResponse(500, ['error' => 'Server Broken']);

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
    $responseA = new MockResponse(200, ['name' => 'Sammyjo20']);
    $responseB = new MockResponse(200, ['name' => 'Alex']);
    $responseC = new MockResponse(500, ['error' => 'Server Broken']);

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

test('a request can be mocked with a url defined using a closure', function () {
    Saloon::fake([
        'tests.saloon.dev/*' => function (PendingRequest $request): MockResponse {
            return new MockResponse(200, ['request' => $request->getUrl()]);
        },
    ]);

    $responseA = (new UserRequest)->send();

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
    expect($responseA->status())->toEqual(200);
});
