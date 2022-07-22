<?php

use Illuminate\Support\Facades\Event;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonLaravel\Events\SaloonRequestSent;
use Sammyjo20\SaloonLaravel\Events\SaloonRequestSending;
use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\UserRequest;

test('events are fired when a request is being sent and when a request has been sent', function () {
    Saloon::fake([
        new MockResponse(['name' => 'Sam'], 200),
    ]);

    Event::fake();

    $response = UserRequest::make()->send();

    Event::assertDispatched(SaloonRequestSending::class, function (SaloonRequestSending $event) use ($response) {
        return $response->getOriginalRequest() === $event->request;
    });

    Event::assertDispatched(SaloonRequestSent::class, function (SaloonRequestSent $event) use ($response) {
        return $response === $event->response && $response->getOriginalRequest() === $event->request;
    });
});
