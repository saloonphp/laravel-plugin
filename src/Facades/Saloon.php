<?php

declare(strict_types=1);

namespace Saloon\Laravel\Facades;

use Saloon\Http\Faking\MockClient;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Saloon\Laravel\Saloon
 *
 * @mixin \Saloon\Laravel\Traits\HasDeprecatedFacadeMethods
 *
 * @method static MockClient fake(array $responses)
 * @method static MockClient mockClient()
 * @method static void assertSent(string|callable $value)
 * @method static void assertNotSent(string|callable $value)
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 */
class Saloon extends Facade
{
    /**
     * Register the Saloon facade
     */
    protected static function getFacadeAccessor(): string
    {
        return 'saloon';
    }
}
