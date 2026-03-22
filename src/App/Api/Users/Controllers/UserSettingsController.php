<?php

declare(strict_types=1);

namespace App\Api\Users\Controllers;

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\UserSettings;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserSettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Request $request, UserSettings $settings): RedirectResponse
    {
        Gate::authorize('update', $request->user());

        // Filter out any null values to avoid overwriting existing settings with null.
        $update = array_filter([
            'player' => $settings->player?->toArray(),
            'general' => $settings->general?->toArray(),
            'appearance' => $settings->appearance?->toArray(),
        ]);

        // Update the user's settings with the provided values.
        (new UpdateUserSettings)->handle($request->user(), $update);

        Inertia::flash([
            'title' => __('Settings updated'),
            'description' => __('Your preferences have been saved.'),
            'type' => 'success',
        ]);

        return back();
    }
}
