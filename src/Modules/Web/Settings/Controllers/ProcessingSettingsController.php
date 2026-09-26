<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Controllers;

use Domain\Videos\Settings\ProcessingSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Web\Settings\Requests\ProcessingSettingsRequest;

class ProcessingSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(ProcessingSettings $settings): JsonResponse
    {
        Gate::authorize('manage-application-settings');

        return response()->json($settings->toArray());
    }

    public function update(ProcessingSettingsRequest $request, ProcessingSettings $settings): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $settings->fill($request->validated());
        $settings->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        toast(title: __('Settings saved'), description: __('Processing settings have been updated successfully.'));

        return back();
    }
}
