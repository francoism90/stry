<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class VideoGroupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Video $video, GroupType $type, Request $request): Response|RedirectResponse
    {
        Gate::authorize('view', $video);

        // Get the currently authenticated user
        $user = Auth::user();

        // Toggle the group association based on the type
        match ($type) {
            GroupType::Favorited => $user->toggleFavorited($video),
            GroupType::Saved => $user->toggleSaved($video),
            default => abort(403),
        };

        return $request->inertia()
            ? redirect()->back()
            : response()->noContent();
    }
}
