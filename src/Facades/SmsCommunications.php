<?php

namespace FmTod\SmsCommunications\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \FmTod\SmsCommunications\SmsCommunications
 */
class SmsCommunications extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \FmTod\SmsCommunications\SmsCommunications::class;
    }
}
