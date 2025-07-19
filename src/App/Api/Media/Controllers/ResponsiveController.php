<?php

declare(strict_types=1);

namespace App\Api\Media\Controllers;

use Domain\Media\Models\Media;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponsiveController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('cache:public;max_age=604800;immutable;etag'),
        ];
    }

    public function __invoke(Media $media, string $conversion = '', string $path = ''): StreamedResponse
    {
        Gate::authorize('view', $media->model);

        abort_if(! $media->hasResponsiveImages($conversion) || blank($path), 404);

        $filesystem = Storage::disk(filled($conversion) ? $media->conversions_disk : $media->disk);

        $directory = app(Filesystem::class)->getResponsiveImagesDirectory($media);

        $path = implode('/', array_filter([$directory, $path]));

        abort_unless($filesystem->exists($path), 404);

        return $filesystem->response($path);
    }
}
