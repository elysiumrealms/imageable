<?php

namespace Elysiumrealms\Imageable\Http\Controllers;

use Elysiumrealms\Imageable\Contracts;
use Elysiumrealms\Imageable\Exceptions;
use Elysiumrealms\Imageable\Models\Imageable;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageableController
{
    /**
     * Get images
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user instanceof Contracts\Imageable) {
            throw new Exceptions\ImageableException(
                'Unsupported imageable model.',
            );
        }

        $paginator = $user->images()
            ->when(
                $request->input('h') || $request->input('w'),
                fn($query) => $query
                    ->with('images', function ($query) use ($request) {
                        $query->where('height', $request->input('h'))
                            ->where('width', $request->input('w'));
                    })
            )
            ->when(
                $request->input('collection'),
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

        $paginator->getCollection()->transform(
            fn($image) => $image->resize(
                $request->input('w'),
                $request->input('h')
            )->toImageable()
        );

        return response()->json($paginator);
    }

    /**
     * Get the image
     *
     * @param Request $request
     * @param string $image
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $image)
    {
        /** @var \Elysiumrealms\Imageable\Models\Imageable $image */
        $image = Imageable::withTrashed()
            ->when(
                $request->input('h') || $request->input('w'),
                fn($query) => $query
                    ->with('images', function ($query) use ($request) {
                        $query->where('height', $request->input('h'))
                            ->where('width', $request->input('w'));
                    })
            )
            ->findOrFail('/' . config('imageable.directory') . '/' . $image);

        if ($image->trashed()) $image->restore();

        $disk = Storage::disk(config('imageable.disk'));
        return Image::make(
            $content = $disk->get($image->resize(
                $request->input('w'),
                $request->input('h')
            )->path)
        )->response()
            ->header(
                'Cache-Control',
                'public, max-age=' . config('imageable.proxy.cache')
            )
            ->header('Expires', gmdate(
                'D, d M Y H:i:s \G\M\T',
                time() + config('imageable.proxy.cache')
            ))
            ->header('Last-Modified', gmdate(
                'D, d M Y H:i:s \G\M\T',
                $image->created_at->timestamp
            ))
            ->header('Etag', "\"" . md5($content) . "\"");
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

                /** @var \Elysiumrealms\Imageable\Models\Imageable $image */
                $image = $user->images()->withTrashed()
                    ->firstOrCreate([
                        'hash' => md5($content),
                        'width' => $image->width(),
                        'height' => $image->height(),
                        'collection' => $collection,
                        'mime_type' => $file->getMimeType(),
                    ], [
                        'path' => $file,
                    ]);

                if ($image->trashed()) $image->restore();

                return $image->toImageable();
            });

        return response()->json($images->toArray(), 201);
    }

    /**
     * Delete images
     *
     * @param Request $request
     * @param string|null $image
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $image = null)
    {
        $images = $request->input('images', [$image]);

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
                collect($images)
                    ->map(fn($image) => "/{$dir}/" . basename($image))
                    ->toArray()
            )
            ->delete();

        return response()->json(['deleted' => $count]);
    }
}
