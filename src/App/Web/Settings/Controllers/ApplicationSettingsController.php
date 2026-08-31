<?php

declare(strict_types=1);

namespace App\Web\Settings\Controllers;

use App\Web\Settings\Requests\ApplicationSettingsRequest;
use Foundation\Settings\GeneralSettings;
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

    public function __invoke(ApplicationSettingsRequest $request, GeneralSettings $settings): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $settings->fill($request->validated());
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
