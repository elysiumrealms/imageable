<?php

namespace Elysiumrealms\Imageable\Models;

use Elysiumrealms\Imageable\Traits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class Imageable extends Model
{
    use HasFactory;
    use Traits\ImageableTrait;

    protected $primaryKey = 'path';
    public $incrementing = false;

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            $model->images()->delete();
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
     * The attributes that are imageable.
     *
     * @var array
     */
    protected $imageable = [
        'path',
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

        $image = Image::make($disk->get($this->path))
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
}
