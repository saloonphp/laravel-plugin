<?php

namespace Sammyjo20\SaloonLaravel\Facades;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @see \Sammyjo20\SaloonLaravel\Saloon
 *
 * @method static \Sammyjo20\SaloonLaravel\Clients\MockClient fake(array $responses)
 * @method static \Sammyjo20\SaloonLaravel\Clients\MockClient mockClient()
 * @method static void assertSent(string|callable $value)
 * @method static void assertNotSent(string|callable $value)
 * @method static void assertSentJson(string $request, array $data)
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 * @method static void record()
 * @method static void stopRecording()
 * @method static bool isRecording()
 * @method static \Sammyjo20\Saloon\Http\SaloonResponse[] getRecordedResponses()
 * @method static \Sammyjo20\Saloon\Http\SaloonResponse getLastRecordedResponse()
 */
class Saloon extends BaseFacade
{
    /**
     * Register the Saloon facade
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'saloon';
    }
}
