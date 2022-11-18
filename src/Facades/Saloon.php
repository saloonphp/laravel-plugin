<?php declare(strict_types=1);

namespace Saloon\Laravel\Facades;

use Saloon\Contracts\SaloonResponse;
use Saloon\Laravel\Http\Faking\MockClient;
use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @see \Sammyjo20\SaloonLaravel\Saloon
 *
 * @method static MockClient fake(array $responses)
 * @method static MockClient mockClient()
 * @method static void assertSent(string|callable $value)
 * @method static void assertNotSent(string|callable $value)
 * @method static void assertSentJson(string $request, array $data)
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 * @method static void record()
 * @method static void stopRecording()
 * @method static bool isRecording()
 * @method static SaloonResponse[] getRecordedResponses()
 * @method static SaloonResponse getLastRecordedResponse()
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
