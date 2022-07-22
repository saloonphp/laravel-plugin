<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\ExtractTags;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\SaloonLaravel\Events\SentSaloonRequest;
use Sammyjo20\SaloonLaravel\Listeners\RecordSaloonToTelescope;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\PostRequest;
use Sammyjo20\SaloonLaravel\Tests\Fixtures\Requests\UserRequest;

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

    // Make sure all fields are present, and it's stored correctly.

    expect($entryContent)->toBeArray();
    expect($entryContent)->toHaveKey('method', 'POST');
    expect($entryContent)->toHaveKey('uri', 'https://tests.saloon.dev/api/post?test_query=hello');
    expect($entryContent)->toHaveKey('headers', ['X-Foo' => 'Bar']);
    expect($entryContent)->toHaveKey('payload', ['name' => 'Sammy']);
    expect($entryContent)->toHaveKey('response_status', 200);
    expect($entryContent)->toHaveKey('response_headers', ['Content-Type' => [0 => 'application/json'], 'X-Foo' => [0 => 'Bar']]);
    expect($entryContent)->toHaveKey('response', ['name' => 'Sammy']);
    expect($entryContent)->toHaveKey('saloon_connector', TestConnector::class);
    expect($entryContent)->toHaveKey('saloon_request', PostRequest::class);

    // Now we need to check the tags

    $tags = DB::table('telescope_entries_tags')->get();

    expect($tags)->toHaveCount(3);

    $saloonTag = $tags->where('tag', 'Saloon')->first();
    $requestTag = $tags->where('tag', 'SaloonRequest:' . PostRequest::class)->first();
    $connectorTag = $tags->where('tag', 'SaloonConnector:' . TestConnector::class)->first();

    expect($saloonTag)->not()->toBeNull();
    expect($requestTag)->not()->toBeNull();
    expect($connectorTag)->not()->toBeNull();
});

test('it will format the response data as json if it can determine the response is JSON', function () {
    $mockClient = new MockClient([
        MockResponse::make('Plain Text Response', 200, ['Content-Type' => 'text/plain'])
    ]);

    UserRequest::make()->send($mockClient);

    $entry = $this->loadTelescopeEntries()->where('type', EntryType::CLIENT_REQUEST)->sole();
    $entryContent = $entry->content;

    expect($entryContent)->toHaveKey('response', 'Plain Text Response');
});
