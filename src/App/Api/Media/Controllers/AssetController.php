<?php

declare(strict_types=1);

namespace App\Api\Media\Controllers;

use Domain\Media\Models\Media;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('cache:public;max_age=604800;immutable;etag'),
        ];
    }

    public function __invoke(Media $media, string $conversion = ''): StreamedResponse
    {
        Gate::authorize('view', $media->model);

        abort_if(filled($conversion) && ! $media->hasGeneratedConversion($conversion), 404);

        $filesystem = Storage::disk(filled($conversion) ? $media->conversions_disk : $media->disk);

        $path = $media->getPathRelativeToRoot($conversion);

        abort_unless($filesystem->exists($path), 404);

        return $filesystem->response($path);
    }
}
