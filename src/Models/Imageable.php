<?php

namespace Elysiumrealms\Imageable\Models;

use Elysiumrealms\Imageable\Exceptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Intervention\Image\Facades\Image as ImageFacade;
use Illuminate\Support\Str;
use Intervention\Image\Image;

class Imageable extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'path';
    public $incrementing = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $dir = config('imageable.directory');
            $disk = Storage::disk(config('imageable.disk'));
            $value = $model->attributes['path'];
            switch (true) {
                case $value instanceof UploadedFile:
                    $disk->put(
                        $model->path = "/${dir}/" .
                            $value->hashName(),
                        $value->get()
                    );
                    break;
                case $value instanceof Image:
                    $disk->put(
                        $model->path = "/${dir}/" .
                            Str::random(40) .
                            '.' . last(explode('/', $value->mime())),
                        $value->encode()
                    );
                    break;
                default:
                    throw new Exceptions\ImageableException(
                        'Unsupported imageable type.'
                    );
            }
        });

        static::deleted(function ($model) {
            $model->images()->delete();
        });

        static::restored(function ($model) {
            $model->images()->restore();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'path',
        'hash',
        'width',
        'height',
        'mime_type',
        'collection',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'disk',
        'hash',
        'mime_type',
        'owner_id',
        'owner_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 序列化日期
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->getDateFormat());
    }

    /**
     * Get the owner of the image.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the images of the image.
     */
    public function images(): HasMany
    {
        return $this->hasMany(static::class, 'hash', 'hash');
    }

    /**
     * Resize the image.
     *
     * @param int|null $width
     * @param int|null $height
     * @return $this
     */
    public function resize($width, $height)
    {
        if (is_null($width) && is_null($height))
            return $this;
        if ($this->width == $width && $this->height == $height)
            return $this;

        if ($image = $this->images
            ->where('width', $width)
            ->where('height', $height)
            ->first()
        )
            return $image;

        $disk = Storage::disk(config('imageable.disk'));

        $image = ImageFacade::make($disk->get($this->path))
            ->resize($width, $height);

        return $this->images()->firstOrCreate(
            [
                'width' => $width,
                'height' => $height,
                'mime_type' => $this->mime_type,
                'collection' => $this->collection,
            ],
            ['path' => $image]
        );
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
        $disk = Storage::disk(config('imageable.disk'));
        $url = call_user_func_array(
            [$disk, 'url'],
            [ltrim($this->attributes['path'], '/')]
        );

        $this->attributes['path'] = app('imageable')->resolve($url);

        return new Fluent($this->attributesToArray(), $this->relations);
    }
}
