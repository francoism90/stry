<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Controllers;

use Domain\Transcodes\Actions\ImportTranscode;
use Domain\Transcodes\Models\Transcode;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TranscodeImportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(Transcode $transcode, Request $request): RedirectResponse
    {
        Gate::authorize('update', $transcode);

        app(ImportTranscode::class)->handle($transcode);

        Inertia::flash([
            'title' => (string) $transcode->file_name,
            'description' => __('Queued for import.'),
            'type' => 'info',
        ]);

        return back();
    }
}
