<?php

declare(strict_types=1);

namespace Saloon\Laravel\Http\Middleware;

use Saloon\Http\Response;
use Saloon\Laravel\Saloon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Lottery;
use Saloon\Http\PendingRequest;
use Illuminate\Support\Facades\Config;
use Saloon\Contracts\ResponseMiddleware;

class PulseResponseMiddleware implements ResponseMiddleware
{
    public function __invoke(Response $response): void
    {
        // Check if Pulse is installed

        if (! class_exists('Laravel\Pulse\Facades\Pulse')) {
            return;
        }

        $pendingRequest = $response->getPendingRequest();

        $requestId = spl_object_id($pendingRequest);
        $startTime = Saloon::$pulseStartTimes[$requestId] ?? null;

        // Calculate duration in milliseconds
        $duration = $startTime !== null ? (int)((microtime(true) - $startTime) * 1000) : null;

        // Clean up start time
        unset(Saloon::$pulseStartTimes[$requestId]);

        // Record to Pulse
        if ($duration !== null) {
            $this->recordToPulse($pendingRequest, $duration);
        }
    }

    /**
     * Record the request to Pulse
     */
    protected function recordToPulse(PendingRequest $pendingRequest, int $duration): void
    {
        $psrRequest = $pendingRequest->createPsrRequest();
        $method = $psrRequest->getMethod();
        $uri = (string) $psrRequest->getUri();

        // Get the recorder configuration
        $recorderClass = \Laravel\Pulse\Recorders\SlowOutgoingRequests::class;
        $config = Config::get('pulse.recorders.'.$recorderClass, []);

        // Check if the recorder is enabled
        if (! ($config['enabled'] ?? true)) {
            return;
        }

        // Check sampling (using Lottery like Pulse does)
        $sampleRate = $config['sample_rate'] ?? 1;
        if (! Lottery::odds($sampleRate)->choose()) {
            return;
        }

        // Check ignore patterns (using same logic as Pulse Ignores trait)
        $ignore = $config['ignore'] ?? [];
        foreach ($ignore as $pattern) {
            if (preg_match($pattern, $uri)) {
                return;
            }
        }

        // Check threshold (using same logic as Pulse Thresholds trait)
        $threshold = $this->getThreshold($uri, $config['threshold'] ?? 100);
        if ($duration < $threshold) {
            return;
        }

        // Group the URI (using same logic as Pulse Groups trait)
        $groupedUri = $this->groupUri($uri, $config['groups'] ?? []);

        $timestamp = CarbonImmutable::now()->getTimestamp();

        // Record to Pulse using the same format as SlowOutgoingRequests recorder
        \Laravel\Pulse\Facades\Pulse::record(
            type: 'slow_outgoing_request',
            key: json_encode([$method, $groupedUri], flags: JSON_THROW_ON_ERROR),
            value: $duration,
            timestamp: $timestamp,
        )->max()->count();
    }

    /**
     * Get the threshold for the given URI (matching Pulse Thresholds trait logic)
     *
     * @param int|array<string, int> $threshold
     */
    protected function getThreshold(string $uri, int|array $threshold): int
    {
        if (! is_array($threshold)) {
            return $threshold;
        }

        // Check for pattern matches
        foreach ($threshold as $pattern => $value) {
            if ($pattern === 'default') {
                continue;
            }

            if (preg_match($pattern, $uri) === 1) {
                return $value;
            }
        }

        return $threshold['default'] ?? 100;
    }

    /**
     * Group the URI according to configured groups (matching Pulse Groups trait logic)
     *
     * @param array<string, string> $groups
     */
    protected function groupUri(string $uri, array $groups): string
    {
        foreach ($groups as $pattern => $replacement) {
            $group = preg_replace($pattern, $replacement, $uri, count: $count);

            if ($count > 0 && $group !== null) {
                return $group;
            }
        }

        return $uri;
    }
}
