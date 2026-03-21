<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use Domain\Videos\Actions\ProcessVideoImport;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoImportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('role:super-admin'),
        ];
    }

    public function __invoke(Request $request): RedirectResponse|Response
    {
        Gate::authorize('create', Video::class);

        // Perform the import action
        app(ProcessVideoImport::class)->handle(
            user: $request->user(),
            disk: Video::getImportDisk()
        );

        if ($request->inertia()) {
            // Notify the user
            Inertia::flash([
                'title' => __('Video import initiated'),
                'description' => __('Files are being processed in the background.'),
            ]);

            return back();
        }

        return response()->noContent();
    }
}
