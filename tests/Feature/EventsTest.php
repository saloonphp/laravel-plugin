<?php

declare(strict_types=1);

use Saloon\Laravel\Facades\Saloon;
use Saloon\Http\Faking\MockResponse;
use Illuminate\Support\Facades\Event;
use Saloon\Laravel\Events\SentSaloonRequest;
use Saloon\Laravel\Events\SendingSaloonRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

test('events are fired when a request is being sent and when a request has been sent', function (string $sender) {
    setSender($sender);

    Saloon::fake([
        new MockResponse(['name' => 'Sam'], 200),
    ]);

    Event::fake();

    $response = TestConnector::make()->send(new UserRequest);

    Event::assertDispatched(SendingSaloonRequest::class, function (SendingSaloonRequest $event) use ($response) {
        return $response->getPendingRequest() === $event->pendingRequest;
    });

    Event::assertDispatched(SentSaloonRequest::class, function (SentSaloonRequest $event) use ($response) {
        return $response === $event->response && $response->getPendingRequest() === $event->pendingRequest;
    });
})->with('senders');
