<?php

declare(strict_types=1);

namespace App\Web\Settings\Controllers;

use App\Web\Playlists\Responses\PlaylistTypeOptionsProperty;
use App\Web\Settings\Requests\PlaylistSettingsRequest;
use Domain\Playlists\Enums\EncryptionMethod;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Enums\ProtectionScheme;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Shared\Enums\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Spatie\LaravelOptions\Options;

class PlaylistSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(PlaylistSettings $settings): JsonResponse
    {
        Gate::authorize('manage-application-settings');

        return response()->json([
            ...$settings->toArray(),
            'type_options' => PlaylistTypeOptionsProperty::options(),
            'encryption_options' => Options::forEnum(EncryptionMethod::class)->nullable('None'),
            'protection_scheme_options' => Options::forEnum(ProtectionScheme::class)->nullable('None'),
        ]);
    }

    public function update(PlaylistSettingsRequest $request, PlaylistSettings $settings): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $validated = $request->validated();

        // Settings::fill() bypasses the mapper's cast pipeline and assigns properties directly,
        // so backed-enum-typed properties need to already be enum instances, not raw strings.
        if (isset($validated['type'])) {
            $validated['type'] = PlaylistType::from($validated['type']);
        }

        if (isset($validated['language'])) {
            $validated['language'] = Language::from($validated['language']);
        }

        if (isset($validated['text_language'])) {
            $validated['text_language'] = Language::from($validated['text_language']);
        }

        if (array_key_exists('encryption', $validated) && $validated['encryption'] !== null) {
            $validated['encryption'] = EncryptionMethod::from($validated['encryption']);
        }

        if (array_key_exists('protection_scheme', $validated) && $validated['protection_scheme'] !== null) {
            $validated['protection_scheme'] = ProtectionScheme::from($validated['protection_scheme']);
        }

        $settings->fill($validated);
        $settings->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        Inertia::flash([
            'title' => __('Settings saved'),
            'description' => __('Playlist settings have been updated successfully.'),
            'type' => 'success',
        ]);

        return back();
    }
}
