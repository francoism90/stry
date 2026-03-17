<?php

declare(strict_types=1);

namespace App\Web\Transcodes\Controllers;

use App\Api\Transcodes\Requests\TranscodeUpdateRequest;
use Domain\Transcodes\Models\Transcode;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TranscodeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function update(Transcode $transcode, TranscodeUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $transcode);

        // Update transcode details
        $transcode->updateOrFail($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $transcode->file_name,
            'description' => __('The transcode has been updated.'),
        ]);

        return back();
    }

    public function destroy(Transcode $transcode): RedirectResponse
    {
        Gate::authorize('delete', $transcode);

        // Delete the transcode
        $transcode->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $transcode->file_name,
            'description' => __('The transcode has been deleted.'),
        ]);

        return back();
    }
}
