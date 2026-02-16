<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Controllers;

use Domain\Transcodes\Actions\ImportTranscode;
use Domain\Transcodes\Models\Transcode;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
            new Middleware('auth:sanctum'),
            new Middleware('role:super-admin'),
        ];
    }

    public function __invoke(ImportTranscode $action, Transcode $transcode, Request $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $transcode);

        // Perform the import action
        $result = $action->handle($transcode);

        return $request->inertia()
            ? Inertia::flash('message', $result['message'])->back()
            : response()->json($result);
    }
}
