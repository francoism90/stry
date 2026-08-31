<?php

declare(strict_types=1);

namespace App\Web\Settings\Controllers;

use App\Web\Settings\Requests\ApplicationSettingsRequest;
use Domain\Shared\Enums\Locale;
use Foundation\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ApplicationSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(GeneralSettings $settings): JsonResponse
    {
        Gate::authorize('manage-application-settings');

        return response()->json($settings->toArray());
    }

    public function update(ApplicationSettingsRequest $request, GeneralSettings $settings): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $validated = $request->validated();

        // Settings::fill() bypasses the mapper's cast pipeline and assigns properties directly,
        // so backed-enum-typed properties need to already be enum instances, not raw strings.
        if (isset($validated['default_locale'])) {
            $validated['default_locale'] = Locale::from($validated['default_locale']);
        }

        $settings->fill($validated);
        $settings->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        Inertia::flash([
            'title' => __('Settings saved'),
            'description' => __('Application settings have been updated successfully.'),
            'type' => 'success',
        ]);

        return back();
    }
}
