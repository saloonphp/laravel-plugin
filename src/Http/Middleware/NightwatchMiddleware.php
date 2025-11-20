<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

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

        // Check if Nightwatch is installed
        if (! class_exists('Laravel\Nightwatch\Facades\Nightwatch')) {
            return;
        }

        // Check if we're using GuzzleSender
        if ($sender instanceof GuzzleSender === false) {
            return;
        }

        $sender->addMiddleware(\Laravel\Nightwatch\Facades\Nightwatch::guzzleMiddleware(), 'nightwatch');

    }

}
