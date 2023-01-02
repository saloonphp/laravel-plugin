<?php

declare(strict_types=1);

use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

test('a request can be made successfully', function () {
    $request = new UserRequest();
    $response = TestConnector::make()->send($request);

    $data = $response->json();

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('a request can handle an exception properly', function () {
    $request = new ErrorRequest();
    $response = TestConnector::make()->send($request);

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(500);
});
