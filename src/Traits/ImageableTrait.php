<?php

namespace Elysiumrealms\Imageable\Traits;

use Elysiumrealms\Imageable\Models;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Fluent;

trait ImageableTrait
{
    /**
     * Convert the model to a Fluent object.
     *
     * @param int|null $width
     * @param int|null $height
     * @return \Illuminate\Support\Fluent
     */
    public function toImageable()
    {
        return new Fluent(array_merge(
            $this->attributesToArray(),
            $this->relations,
            [
                'images' => $this->images
                    ->map(fn($image) => $image->toImageable())
            ]
        ));
    }

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
