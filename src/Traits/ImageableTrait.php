<?php

namespace Elysiumrealms\Imageable\Traits;

use Elysiumrealms\Imageable\Models;
use Elysiumrealms\Imageable\Exceptions;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Intervention\Image\Image;

trait ImageableTrait
{
    /**
     * Boot the imageable trait.
     */
    public static function bootImageableTrait()
    {
        static::creating(function ($model) {

            if (!property_exists($model, 'imageable'))
                return;

            $dir = config('imageable.directory');
            $disk = Storage::disk(config('imageable.disk'));
            foreach ($model->imageable as $key) {
                $value = $model->attributes[$key];
                switch (true) {
                    case $value instanceof UploadedFile:
                        $disk->put(
                            $model->{$key} = "/${dir}/" .
                                $value->hashName(),
                            $value->get()
                        );
                        break;
                    case $value instanceof Image:
                        $disk->put(
                            $model->{$key} = "/${dir}/" .
                                Str::random(40) .
                                '.' . last(explode('/', $value->mime())),
                            (string)$value
                        );
                        break;
                    default:
                        throw new Exceptions\ImageableException(
                            'Unsupported imageable type.'
                        );
                }
            }
        });

        static::deleted(function ($model) {

            if (!property_exists($model, 'imageable'))
                return;

            $disk = Storage::disk(config('imageable.disk'));
            foreach ($model->imageable as $key) {
                $disk->delete($model->attributes[$key]);
            }
        });
    }

    /**
     * Convert the model to a Fluent object.
     *
     * @param int|null $width
     * @param int|null $height
     * @return \Illuminate\Support\Fluent
     */
    public function toImageable()
    {
        if (!property_exists($this, 'imageable'))
            return new Fluent(
                $this->attributes,
                $this->relations
            );

        $disk = Storage::disk(config('imageable.disk'));
        foreach ($this->imageable as $key) {

            $url = call_user_func_array(
                [$disk, 'url'],
                [ltrim($this->attributes[$key], '/')]
            );

            $this->attributes[$key] = app('imageable')->resolve($url);
        }

        return new Fluent($this->attributesToArray(), $this->relations);
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
