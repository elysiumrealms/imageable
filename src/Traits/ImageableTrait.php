<?php

namespace Elysiumrealms\Imageable\Traits;

use Elysiumrealms\Imageable\Models;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Fluent;

trait ImageableTrait
{
    /**
     * Images relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Models\Imageable::class, 'owner');
    }
}
