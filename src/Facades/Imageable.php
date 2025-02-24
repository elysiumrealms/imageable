<?php

namespace Elysiumrealms\Imageable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Elysiumrealms\Imageable\Service\ImageableService advertise($to, $from = null)
 *
 * @see \Elysiumrealms\Imageable\Service\ImageableService
 */
class Imageable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'imageable';
    }
}
