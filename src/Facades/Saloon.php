<?php

namespace Sammyjo20\SaloonLaravel\Facades;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * @see \Sammyjo20\SaloonLaravel\Saloon
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
