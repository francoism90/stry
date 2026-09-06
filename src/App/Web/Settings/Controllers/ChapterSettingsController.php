<?php

declare(strict_types=1);

namespace App\Web\Settings\Controllers;

use App\Web\Settings\Requests\ChapterSettingsRequest;
use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Settings\ChapterSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ChapterSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(ChapterSettings $settings): JsonResponse
    {
        Gate::authorize('manage-application-settings');

        return response()->json($settings->toArray());
    }

    public function update(ChapterSettingsRequest $request, ChapterSettings $settings): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $validated = $request->validated();

        // Settings::fill() bypasses the mapper's cast pipeline and assigns properties directly,
        // so the backed-enum-typed property needs to already be an enum instance, not a raw string.
        if (isset($validated['default_type'])) {
            $validated['default_type'] = ChapterType::from($validated['default_type']);
        }

        // The patterns textarea posts a JSON string; decode it before it hits the array property,
        // otherwise it gets stored as a JSON-encoded string instead of an actual map.
        if (isset($validated['patterns']) && is_string($validated['patterns'])) {
            $validated['patterns'] = json_decode($validated['patterns'], true);
        }

        $settings->fill($validated);
        $settings->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        Inertia::flash([
            'title' => __('Settings saved'),
            'description' => __('Chapter settings have been updated successfully.'),
            'type' => 'success',
        ]);

        return back();
    }
}
