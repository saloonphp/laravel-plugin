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

        // Ensure that the Nightwatch middleware is only registered once on the
        // handler stack. For long-lived connectors, this middleware may be
        // invoked multiple times, so remove any existing Nightwatch
        // middleware before re-adding it to prevent oversampling.
        $handlerStack = $sender->getHandlerStack();
        $handlerStack->remove('nightwatch');

        $sender->addMiddleware(\Laravel\Nightwatch\Facades\Nightwatch::guzzleMiddleware(), 'nightwatch');

    }

}
