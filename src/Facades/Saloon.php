<?php

namespace Sammyjo20\SaloonLaravel\Facades;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @see \Sammyjo20\SaloonLaravel\Saloon
 * @method fake(array $responses)
 * @method mockClient()
 * @method assertSent(string|callable $value)
 * @method assertNotSent(string|callable $value)
 * @method assertSentJson(string $request, array $data)
 * @method assertNothingSent()
 * @method assertSentCount(int $count)
 * @method record()
 * @method stopRecording()
 * @method isRecording()
 * @method getRecordedResponses()
 * @method getLastRecordedResponse()
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
