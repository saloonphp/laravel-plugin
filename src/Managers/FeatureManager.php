<?php

namespace Sammyjo20\SaloonLaravel\Managers;

use Sammyjo20\Saloon\Http\Middleware\MockMiddleware;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Managers\LaravelManager;

class FeatureManager
{
    /**
     * The LaravelManager provided by Saloon.
     *
     * @var LaravelManager
     */
    protected LaravelManager $laravelManager;

    /**
     * The SaloonRequest provided by Saloon
     *
     * @var SaloonRequest
     */
    protected SaloonRequest $request;

    /**
     * @param LaravelManager $laravelManager
     * @param SaloonRequest $request
     */
    public function __construct(LaravelManager $laravelManager, SaloonRequest $request)
    {
        $this->laravelManager = $laravelManager;
        $this->request = $request;
    }

    /**
     * Boot the mocking feature.
     *
     * @return void
     */
    public function bootMockingFeature(): void
    {
        $mockManager = MockManager::resolve();
        $isMocking = $mockManager->isMocking();

        if ($isMocking === false) {
            return;
        }

        $response = $mockManager->guessNextResponse($this->getRequest());

        $this->laravelManager->setIsMocking($isMocking);
        $this->laravelManager->addHandler('saloonMockMiddleware', new MockMiddleware($response));
    }

    /**
     * Return the Laravel Manager.
     *
     * @return LaravelManager
     */
    public function getLaravelManager(): LaravelManager
    {
        return $this->laravelManager;
    }

    /**
     * Return the Saloon request
     *
     * @return SaloonRequest
     */
    public function getRequest(): SaloonRequest
    {
        return $this->request;
    }
}
