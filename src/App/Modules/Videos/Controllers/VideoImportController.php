<?php

declare(strict_types=1);

namespace App\Modules\Videos\Controllers;

use Domain\Videos\Actions\ProcessVideoImport;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoImportController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request): RedirectResponse
    {
        Gate::authorize('create', Video::class);

        app(ProcessVideoImport::class)->handle(
            user: $request->user(),
            disk: Video::getImportDisk()
        );

        Inertia::flash([
            'title' => __('Video import initiated'),
            'description' => __('Files are being processed in the background.'),
            'type' => 'info',
        ]);

        return back();
    }
}
