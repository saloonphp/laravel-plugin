<?php

declare(strict_types=1);

use Saloon\Laravel\Facades\Saloon;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

test('you can record a response and you can get the recorded responses', function (string $sender) {
    setSender($sender);

    Saloon::fake([
        MockResponse::make(),
    ]);

    Saloon::record();

    expect(Saloon::isRecording())->toBeTrue();

    $response = TestConnector::make()->send(new UserRequest);

    $recorded = Saloon::getRecordedResponses();

    expect($recorded)->toBeArray();
    expect($recorded)->toHaveCount(1);
    expect($recorded[0])->toEqual($response);
})->with('senders');

test('you can get the last recorded response', function (string $sender) {
    setSender($sender);

    Saloon::fake([
        MockResponse::make(['id' => 1]),
        MockResponse::make(['id' => 2]),
    ]);

    Saloon::record();

    expect(Saloon::isRecording())->toBeTrue();

    $connector = new TestConnector;
    $connector->send(new UserRequest());
    $connector->send(new UserRequest());

    $recorded = Saloon::getRecordedResponses();

    expect($recorded)->toBeArray();
    expect($recorded)->toHaveCount(2);

    $lastResponse = Saloon::getLastRecordedResponse();

    expect($lastResponse->json())->toEqual(['id' => 2]);
})->with('senders');

test('you can stop recording', function (string $sender) {
    setSender($sender);

    Saloon::fake([
        MockResponse::make(['id' => 1]),
        MockResponse::make(['id' => 2]),
    ]);

    $connector = new TestConnector;

    Saloon::record();

    expect(Saloon::isRecording())->toBeTrue();

    $connector->send(new UserRequest());

    Saloon::stopRecording();

    expect(Saloon::isRecording())->toBeFalse();

    $connector->send(new UserRequest());

    $recorded = Saloon::getRecordedResponses();

    expect($recorded)->toBeArray();
    expect($recorded)->toHaveCount(1);

    $lastResponse = Saloon::getLastRecordedResponse();

    expect($lastResponse->json())->toEqual(['id' => 1]);
})->with('senders');
