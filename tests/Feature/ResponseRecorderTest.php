<?php declare(strict_types=1);

use Saloon\Http\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Saloon\Laravel\Tests\Fixtures\Requests\UserRequest;

test('you can record a response and you can get the recorded responses', function () {
    Saloon::fake([
        MockResponse::make(),
    ]);

    Saloon::record();

    expect(Saloon::isRecording())->toBeTrue();

    $response = UserRequest::make()->send();

    $recorded = Saloon::getRecordedResponses();

    expect($recorded)->toBeArray();
    expect($recorded)->toHaveCount(1);
    expect($recorded[0])->toEqual($response);
});

test('you can get the last recorded response', function () {
    Saloon::fake([
        MockResponse::make(['id' => 1]),
        MockResponse::make(['id' => 2]),
    ]);

    Saloon::record();

    expect(Saloon::isRecording())->toBeTrue();

    UserRequest::make()->send();
    UserRequest::make()->send();

    $recorded = Saloon::getRecordedResponses();

    expect($recorded)->toBeArray();
    expect($recorded)->toHaveCount(2);

    $lastResponse = Saloon::getLastRecordedResponse();

    expect($lastResponse->json())->toEqual(['id' => 2]);
});

test('you can stop recording', function () {
    Saloon::fake([
        MockResponse::make(['id' => 1]),
        MockResponse::make(['id' => 2]),
    ]);

    Saloon::record();

    expect(Saloon::isRecording())->toBeTrue();

    UserRequest::make()->send();

    Saloon::stopRecording();

    expect(Saloon::isRecording())->toBeFalse();

    UserRequest::make()->send();

    $recorded = Saloon::getRecordedResponses();

    expect($recorded)->toBeArray();
    expect($recorded)->toHaveCount(1);

    $lastResponse = Saloon::getLastRecordedResponse();

    expect($lastResponse->json())->toEqual(['id' => 1]);
});
