<?php

use Illuminate\Support\Facades\Event;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonLaravel\Events\SentSaloonRequest;
use Sammyjo20\SaloonLaravel\Events\SendingSaloonRequest;
use Sammyjo20\SaloonLaravel\Facades\Saloon;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\UserRequest;

test('events are fired when a request is being sent and when a request has been sent', function () {
    Saloon::fake([
        new MockResponse(['name' => 'Sam'], 200),
    ]);

    Event::fake();

    $response = UserRequest::make()->send();

    Event::assertDispatched(SendingSaloonRequest::class, function (SendingSaloonRequest $event) use ($response) {
        return $response->getOriginalRequest() === $event->request;
    });

    Event::assertDispatched(SentSaloonRequest::class, function (SentSaloonRequest $event) use ($response) {
        return $response === $event->response && $response->getOriginalRequest() === $event->request;
    });
});
