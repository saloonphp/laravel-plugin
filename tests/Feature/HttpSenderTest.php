<?php

use Illuminate\Support\Facades\Http;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\JsonRequest;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\StringRequest;
use Symfony\Component\HttpClient\Response\MockResponse;

// Todo: Add other sender tests for query parameters and options...

test('a request can be sent with json body', function () {
    $request = new JsonRequest();

    $request->body()->add('yee', 'haw');
    $request->body()->add('name', 'Sam');
    $request->body()->add('country', 'UK');

    $response = $request->send();
    $pendingRequest = $response->getPendingSaloonRequest();

    expect($pendingRequest->body())->toEqual($request->body());
    expect($response->status())->toEqual(200);
    expect($response->json())->toEqual($request->body()->all());
});

test('a request can be sent with string body', function () {
    $request = new StringRequest;
    $request->body()->set('Hello World');

    // Todo

    $response = $request->send();
    $pendingRequest = $response->getPendingSaloonRequest();

    expect($pendingRequest->body())->toEqual($request->body());
    expect($response->status())->toEqual(200);
    expect($response->body())->toEqual($request->body()->all());
});

test('files can be attached to a request and it will be sent', function () {

});
