<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Laravel\Facades\Saloon;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ConnectException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Tests\Fixtures\Connectors\InvalidConnectionConnector;

test('you can create a pool on a connector', function (string $sender) {
    setSender($sender);

    $connector = new TestConnector;
    $count = 0;

    expect($connector->sender())->toBeInstanceOf($sender);

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
    ]);

    $pool->setConcurrency(5);

    $pool->withResponseHandler(function (ResponseContract $response) use (&$count) {
        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ]);

        $count++;
    });

    $promise = $pool->send();

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $promise->wait();

    expect($count)->toEqual(5);
})->with('senders');

test('if a pool has a request that cannot connect it will be caught in the handleException callback', function (string $sender) {
    setSender($sender);

    $connector = new InvalidConnectionConnector;
    $count = 0;

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
    ]);

    $pool->setConcurrency(5);

    $pool->withExceptionHandler(function (FatalRequestException $ex) use (&$count) {
        expect($ex)->toBeInstanceOf(FatalRequestException::class);
        expect($ex->getPrevious())->toBeInstanceOf(ConnectException::class);
        expect($ex->getPendingRequest())->toBeInstanceOf(PendingRequest::class);

        $count++;
    });

    $promise = $pool->send();

    $promise->wait();

    expect($count)->toEqual(5);
})->with('senders');

test('you can use pool with a mock client added and it wont send real requests', function () {
    $mockResponses = [
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Charlotte']),
        MockResponse::make(['name' => 'Mantas']),
        MockResponse::make(['name' => 'Emily']),
        MockResponse::make(['name' => 'Error'], 500),
    ];

    Saloon::fake($mockResponses);

    $connector = new TestConnector;
    $successCount = 0;
    $errorCount = 0;

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new ErrorRequest,
    ]);

    $pool->setConcurrency(6);

    $pool->withResponseHandler(function (ResponseContract $response) use (&$successCount, $mockResponses) {
        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual($mockResponses[$successCount]->getBody()->all());

        $successCount++;
    });

    $pool->withExceptionHandler(function (RequestException $exception) use (&$errorCount) {
        $response = $exception->getResponse();

        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual(['name' => 'Error']);

        $errorCount++;
    });

    $promise = $pool->send();

    $promise->wait();

    expect($successCount)->toEqual(4);
    expect($errorCount)->toEqual(1);
});
