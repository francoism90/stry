<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Controllers;

use Domain\Videos\Settings\ReelSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Web\Settings\Requests\ReelSettingsRequest;

class ReelSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(ReelSettings $settings): JsonResponse
    {
        Gate::authorize('manage-application-settings');

        return response()->json($settings->toArray());
    }

    public function update(ReelSettingsRequest $request, ReelSettings $settings): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $settings->fill($request->validated());
        $settings->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        toast(title: __('Settings saved'), description: __('Reel settings have been updated successfully.'));

        return back();
    }
}
