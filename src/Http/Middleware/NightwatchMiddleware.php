<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\PendingRequest;
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
        if (! class_exists('Nightwatch\\Nightwatch')) {
            return;
        }

        // Check if we're using GuzzleSender
        if ($sender instanceof \Saloon\Http\Senders\GuzzleSender === false) {
            return;
        }

        $sender->addMiddleware(\Nightwatch\Nightwatch::guzzleMiddleware(), 'nightwatch');
    }

}
