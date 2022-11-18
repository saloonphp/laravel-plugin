<?php declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Saloon\Http\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Saloon\Laravel\Events\SentSaloonRequest;
use Saloon\Laravel\Events\SendingSaloonRequest;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;

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
