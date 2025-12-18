<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Laravel\Saloon;
use Saloon\Http\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Contracts\RequestMiddleware;

class NightwatchMiddleware implements RequestMiddleware
{
    /**
     * Apply Nightwatch middleware to Guzzle requests when using GuzzleSender
     */
    public function __invoke(PendingRequest $pendingRequest): void
    {
        $sender = $pendingRequest->getConnector()->sender();

        // Check if we're using the Guzzle Sender, Nightwatch is installed and
        // if the middleware hasn't been registered yet.

        if (
            class_exists('Laravel\Nightwatch\Facades\Nightwatch') === false
            || $sender instanceof GuzzleSender === false
            || isset(Saloon::$registeredSenders[$senderId = spl_object_id($sender)]['nightwatch']) === true
        ) {
            return;
        }

        $sender->addMiddleware(\Laravel\Nightwatch\Facades\Nightwatch::guzzleMiddleware(), 'nightwatch');

        Saloon::$registeredSenders[$senderId]['nightwatch'] = true;
    }
}
