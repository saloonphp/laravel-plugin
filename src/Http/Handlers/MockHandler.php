<?php

namespace Sammyjo20\SaloonLaravel\Http\Handlers;

use Psr\Http\Message\RequestInterface;
use Sammyjo20\SaloonLaravel\Managers\SaloonMockManager;

class MockHandler
{
    /**
     * Boot up the mock handler...
     *
     * @param callable $handler
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $mockManager = SaloonMockManager::resolve();

            ray('In the handler', $mockManager->pullFromSequence());

            return $handler($request, $options);
        };
    }
}
