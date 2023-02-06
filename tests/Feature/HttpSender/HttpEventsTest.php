<?php

declare(strict_types=1);

use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Saloon\Laravel\Http\Senders\HttpSender;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;

test('the http events are fired when using the http sender', function () {
    Config::set('saloon.default_sender', HttpSender::class);

    Event::fake();

    $connector = new TestConnector;
    $responseA = $connector->send(new UserRequest);
    $responseB = $connector->send(new UserRequest);

    expect($responseA->status())->toBe(200);
    expect($responseB->status())->toBe(200);

    Event::assertDispatched(RequestSending::class, 2);
    Event::assertDispatched(ResponseReceived::class, 2);
});

test('the http events are fired when using the http sender with asynchronous events', function () {
    Config::set('saloon.default_sender', HttpSender::class);

    Event::fake();

    $connector = new TestConnector;
    $responseA = $connector->sendAsync(new UserRequest)->wait();
    $responseB = $connector->sendAsync(new UserRequest)->wait();

    expect($responseA->status())->toBe(200);
    expect($responseB->status())->toBe(200);

    Event::assertDispatched(RequestSending::class, 2);
    Event::assertDispatched(ResponseReceived::class, 2);
});
