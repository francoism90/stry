<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Controllers;

use Domain\Shared\Enums\SnapshotFrequency;
use Foundation\Settings\SnapshotSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Web\Settings\Requests\SnapshotSettingsRequest;

class SnapshotSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(SnapshotSettings $settings): JsonResponse
    {
        Gate::authorize('manage-application-settings');

        return response()->json($settings->toArray());
    }

    public function update(SnapshotSettingsRequest $request, SnapshotSettings $settings): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $validated = $request->validated();

        // Settings::fill() bypasses the mapper's cast pipeline and assigns properties directly,
        // so backed-enum-typed properties need to already be enum instances, not raw strings.
        if (isset($validated['frequency'])) {
            $validated['frequency'] = SnapshotFrequency::from($validated['frequency']);
        }

        $settings->fill($validated);
        $settings->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        toast(title: __('Settings saved'), description: __('Snapshot settings have been updated successfully.'));

        return back();
    }
}
