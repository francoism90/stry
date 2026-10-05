<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Controllers;

use Domain\Shared\Enums\Language;
use Domain\Videos\Settings\PlaybackSettings;
use Foxws\Media\Encoding\Ladder;
use Foxws\Media\Encoding\Rendition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Web\Settings\Requests\PlaybackSettingsRequest;

class PlaybackSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(PlaybackSettings $settings): JsonResponse
    {
        Gate::authorize('manage-application-settings');

        return response()->json([
            ...$settings->toArray(),
            'rendition_options' => array_map(fn (Rendition $rendition): array => [
                'value' => $rendition->height,
                'label' => "{$rendition->height}p",
            ], Ladder::standard()->renditions),
        ]);
    }

    public function update(PlaybackSettingsRequest $request, PlaybackSettings $settings): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $validated = $request->validated();

        // Settings::fill() bypasses the mapper's cast pipeline and assigns properties directly,
        // so backed-enum-typed properties need to already be enum instances, not raw strings.
        if (isset($validated['text_language'])) {
            $validated['text_language'] = Language::from($validated['text_language']);
        }

        // Largest first, so the renditions compare the same however they were picked
        if (isset($validated['renditions'])) {
            $heights = array_map(intval(...), (array) $validated['renditions']);
            rsort($heights);

            $validated['renditions'] = $heights;
        }

        $settings->fill($validated);
        $settings->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        toast(title: __('Settings saved'), description: __('Playback settings have been updated successfully.'));

        return back();
    }
}
