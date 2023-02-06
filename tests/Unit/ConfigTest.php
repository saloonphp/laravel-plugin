<?php

use Illuminate\Support\Facades\Config;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Laravel\Http\Senders\HttpSender;
use Saloon\Laravel\Tests\Fixtures\Connectors\TestConnector;

test('customising the config will specify the default sender', function () {
    $connector = new TestConnector;
    $sender = $connector->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);

    Config::set('saloon.default_sender', HttpSender::class);

    $connector = new TestConnector;
    $sender = $connector->sender();

    expect($sender)->toBeInstanceOf(HttpSender::class);
});
