<?php

declare(strict_types=1);

use Saloon\Laravel\Facades\Saloon;
use Saloon\Http\Faking\FakeResponse;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

test('if a simulated response payload was provided before mock response it will take priority', function () {
    Saloon::fake([
        new MockResponse(['name' => 'Sam'], 200, ['X-Greeting' => 'Howdy']),
    ]);

    $fakeResponse = new FakeResponse(['name' => 'Gareth'], 201, ['X-Greeting' => 'Hello']);

    $request = new UserRequest;
    $request->middleware()->onRequest(fn () => $fakeResponse);

    $response = TestConnector::make()->send($request);

    expect($response->json())->toEqual(['name' => 'Gareth']);
    expect($response->status())->toEqual(201);
    expect($response->header('X-Greeting'))->toEqual('Hello');
});
