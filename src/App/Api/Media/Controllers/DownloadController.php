<?php

declare(strict_types=1);

namespace App\Api\Media\Controllers;

use Domain\Media\Models\Media;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('role:super-admin'),
        ];
    }

    public function __invoke(Media $media, Request $request): BinaryFileResponse|StreamedResponse
    {
        Gate::authorize('update', $media);

        return $media->toResponse($request);
    }
}
