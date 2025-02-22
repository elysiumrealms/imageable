<?php

namespace Elysiumrealms\Imageable\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Imageable
{
    /**
     * Get the images of the model.
     */
    public function images(): MorphMany;
}
