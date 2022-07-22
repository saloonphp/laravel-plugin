<?php

use Illuminate\Support\Facades\Event;
use Laravel\Telescope\EntryType;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonLaravel\Events\SentSaloonRequest;
use Sammyjo20\SaloonLaravel\Listeners\RecordSaloonToTelescope;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\PostRequest;

beforeEach(function () {
    Event::listen(SentSaloonRequest::class, RecordSaloonToTelescope::class);
});

test('when a request is made - it is reported to telescope', function () {
    $request = new PostRequest;

    $mockClient = new MockClient([
        MockResponse::fromRequest($request),
    ]);

    $request->send($mockClient);

    $entries = $this->loadTelescopeEntries()->where('type', EntryType::CLIENT_REQUEST);

    expect($entries)->toHaveCount(1);

    $entry = $entries->sole();
    $entryContent = $entry->content;

    // Make sure all fields are present and it's stored correctly.

    dd($entry);

    expect($entryContent)->toBeArray();
    expect($entryContent)->toHaveKey('method', 'POST');
    expect($entryContent)->toHaveKey('uri', 'https://tests.saloon.dev/api/post');
    expect($entryContent)->toHaveKey('headers', ['X-Foo' => 'Bar']);
    expect($entryContent)->toHaveKey('payload', ['name' => 'Sammy']);
    expect($entryContent)->toHaveKey('response_status', 200);
    expect($entryContent)->toHaveKey('response_headers', ['Content-Type' => [0 => 'application/json'], 'X-Foo' => [0 => 'Bar']]);
    expect($entryContent)->toHaveKey('response', ['name' => 'Sammy']);
    expect($entryContent)->toHaveKey('saloon_connector', TestConnector::class);
    expect($entryContent)->toHaveKey('saloon_request', PostRequest::class);
});

test('query parameters are shown in the url of the telescope report', function () {

});

test('it will format the response data as json if it can find the correct content type header', function () {
    // Test it won't format without.
});
