<?php

namespace Sammyjo20\SaloonLaravel\Middleware;

use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Helpers\Pipeline;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\SaloonLaravel\Managers\FeatureManager;

class SaloonLaravelMiddleware
{
    /**
     * Handle Laravel Actions
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return PendingSaloonRequest
     * @throws SaloonNoMockResponsesProvidedException
     */
    public function __invoke(PendingSaloonRequest $pendingRequest): PendingSaloonRequest
    {
        $manager = new FeatureManager($pendingRequest);

        $manager
            ->bootMockingFeature()
            ->bootRecordingFeature()
            ->bootEventTriggers();

        return $pendingRequest;
    }
}
