<?php declare(strict_types=1);

use Saloon\Laravel\Tests\Fixtures\Requests\JsonRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\StringRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\MultipartRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\FormParamsRequest;

// Todo: Add other sender tests for query parameters and options...

test('a request can be sent with json body', function () {
    $request = new JsonRequest();

    $request->body()->add('yee', 'haw');
    $request->body()->add('name', 'Sam');
    $request->body()->add('country', 'UK');

    $response = $request->send();
    $pendingRequest = $response->getPendingRequest();

    expect($pendingRequest->body())->toEqual($request->body());
    expect($response->status())->toEqual(200);
    expect($response->json())->toEqual($request->body()->all());
});

test('a request can be sent with string body', function () {
    $request = new StringRequest;
    $request->body()->set('Hello World');

    // Todo

    $response = $request->send();
    $pendingRequest = $response->getPendingRequest();

    expect($pendingRequest->body())->toEqual($request->body());
    expect($response->status())->toEqual(200);
    expect($response->body())->toEqual($request->body()->all());
});

test('a request can be sent with multipart body', function () {
    $request = new MultipartRequest();

    $request->body()->add('yee', 'haw');
    $request->body()->add('name', 'Sam');
    $request->body()->add('country', 'UK');

    // Todo

    $response = $request->send();
    $pendingRequest = $response->getPendingRequest();

    expect($pendingRequest->body())->toEqual($request->body());
    expect($response->status())->toEqual(200);
    expect($response->body())->toEqual($request->body()->all());
});

test('a request can be sent with a form params body', function () {
    $request = new FormParamsRequest();

    $request->body()->add('yee', 'haw');
    $request->body()->add('name', 'Sam');
    $request->body()->add('country', 'UK');

    // Todo

    $response = $request->send();
    $pendingRequest = $response->getPendingRequest();

    expect($pendingRequest->body())->toEqual($request->body());
    expect($response->status())->toEqual(200);
    expect($response->body())->toEqual($request->body()->all());
});

test('files can be attached to a request and it will be sent', function () {
});
