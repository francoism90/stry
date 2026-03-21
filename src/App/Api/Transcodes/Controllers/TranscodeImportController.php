<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Controllers;

use Domain\Transcodes\Actions\ImportTranscode;
use Domain\Transcodes\Models\Transcode;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TranscodeImportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('role:super-admin'),
        ];
    }

    public function __invoke(Transcode $transcode, Request $request): RedirectResponse|Response
    {
        Gate::authorize('update', $transcode);

        // Perform the import action
        app(ImportTranscode::class)->handle($transcode);

        if ($request->inertia()) {
            // Notify the user
            Inertia::flash([
                'title' => (string) $transcode->file_name,
                'description' => __('Queued for import.'),
            ]);

            return back();
        }

        return response()->noContent();
    }
}
