<?php

declare(strict_types=1);

namespace App\Web\Settings\Controllers;

use App\Web\Settings\Requests\ProcessingSettingsRequest;
use Domain\Videos\Settings\ProcessingSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

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

        Inertia::flash([
            'title' => __('Settings saved'),
            'description' => __('Processing settings have been updated successfully.'),
            'type' => 'success',
        ]);

        return back();
    }
}
