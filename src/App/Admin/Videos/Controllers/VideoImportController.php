<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use Domain\Videos\Actions\CreateVideosByImport;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoImportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(CreateVideosByImport $action, Request $request): RedirectResponse
    {
        Gate::authorize('create', Video::class);

        // Perform the import action
        $result = $action->handle($request->user());

        // Flash message
        Inertia::flash('message', $result['message'] ?? __('Import failed'));

        return back();
    }
}
