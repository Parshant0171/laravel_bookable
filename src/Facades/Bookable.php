<?php

namespace Jgu\Bookable\Facades;

use Illuminate\Support\Facades\Facade;

class Bookable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'bookable';
    }
}
