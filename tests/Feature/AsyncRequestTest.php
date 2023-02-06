<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Laravel\Tests\Fixtures\Responses\UserData;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Laravel\Tests\Fixtures\Responses\UserResponse;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequestWithCustomResponse;

test('an asynchronous request can be made successfully', function (string $sender) {
    setSender($sender);

    $promise = TestConnector::make()->sendAsync(new UserRequest);

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(Response::class);

    $data = $response->json();

    expect($response->getPendingRequest()->isAsynchronous())->toBeTrue();
    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
})->with('senders');

test('an asynchronous request can handle an exception properly', function (string $sender) {
    setSender($sender);

    $promise = TestConnector::make()->sendAsync(new ErrorRequest);

    $this->expectException(RequestException::class);

    $promise->wait();
})->with('senders');

test('an asynchronous response will still be passed through response middleware', function (string $sender) {
    setSender($sender);

    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $request = new UserRequest();

    Response::macro('setValue', function ($value) {
        $this->value = $value;
    });

    Response::macro('getValue', function () {
        return $this->value;
    });

    $request->middleware()->onResponse(function (Response $response) {
        $response->setValue(true);
    });

    $connector = new TestConnector;

    $promise = $connector->sendAsync($request, $mockClient);
    $response = $promise->wait();

    expect($response->getValue())->toBeTrue();
})->with('senders');

test('an asynchronous request will return a custom response', function (string $sender) {
    setSender($sender);

    $mockClient = new MockClient([
        MockResponse::make(['foo' => 'bar']),
    ]);

    $connector = new TestConnector;
    $request = new UserRequestWithCustomResponse();

    $promise = $connector->sendAsync($request, $mockClient);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(UserResponse::class);
    expect($response)->customCastMethod()->toBeInstanceOf(UserData::class);
    expect($response)->foo()->toBe('bar');
})->with('senders');
