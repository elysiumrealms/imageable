<?php

namespace Elysiumrealms\Imageable\Http\Controllers;

use Elysiumrealms\Imageable\Contracts;
use Elysiumrealms\Imageable\Exceptions;
use Elysiumrealms\Imageable\Models\Imageable;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class ImageableController
{
    /**
     * Get images
     *
     * @param Request $request
     * @param string $collection
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $collection)
    {
        $user = $request->user();

        if (!$user instanceof Contracts\Imageable) {
            throw new Exceptions\ImageableException(
                'Unsupported imageable model.',
            );
        }

        $images = $user->images()
            ->when(
                $collection,
                fn($query, $value)
                => $query->where(
                    'collection',
                    $value
                )
            )
            ->paginate(
                $request->input('per_page', 10),
                ['*'],
                'page',
                $request->input('page', 1)
            );

        $images->getCollection()
            ->transform(fn($image) => $image->resize(
                $request->input('width'),
                $request->input('height')
            )->toImageable());

        return response()->json($images);
    }

    /**
     * Resize image
     *
     * @param Request $request
     * @param Imageable $imageable
     * @return \Illuminate\Http\JsonResponse
     */
    public function resize(Request $request, Imageable $imageable)
    {
        $request->validate([
            'width' => 'required|integer',
            'height' => 'required|integer',
        ]);

        $user = $request->user();

        if (!$user instanceof Contracts\Imageable) {
            throw new Exceptions\ImageableException(
                'Unsupported imageable model.',
            );
        }

        $image = $user->images()
            ->where('hash', $imageable->hash)
            ->first();

        return response()->json(
            $image->resize(
                $request->input('width'),
                $request->input('height')
            )->toImageable()
        );
    }

    /**
     * Upload images
     *
     * @param Request $request
     * @param string|null $collection
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, $collection)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image',
        ]);

        $user = $request->user();

        if (!$user instanceof Contracts\Imageable) {
            throw new Exceptions\ImageableException(
                'Unsupported imageable model.',
            );
        }

        $images = collect($request->file('images'))
            ->map(function (UploadedFile $file) use ($user, $collection) {

                $image = Image::make($content = $file->get());

                $image = $user->images()->firstOrCreate([
                    'hash' => md5($content),
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'collection' => $collection,
                    'mime_type' => $file->getMimeType(),
                ], [
                    'path' => $file,
                ]);

                return $image->toImageable();
            });

        return response()->json($images->toArray(), 201);
    }

    /**
     * Delete images
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user instanceof Contracts\Imageable) {
            throw new Exceptions\ImageableException(
                'Unsupported imageable model.',
            );
        }

        $dir = config('imageable.directory');
        $count = $user->images()
            ->whereIn(
                'path',
                collect(
                    $request->input('images')
                )->map(fn($image) => "/{$dir}/" . basename($image))
            )
            ->get()->each->delete();

        return response()->json(['deleted' => $count]);
    }
}
