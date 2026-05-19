<?php

declare(strict_types=1);

namespace App\Web\Users\Controllers;

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\UserSettings;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserSettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Request $request, UserSettings $settings): Response|RedirectResponse
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

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        Inertia::flash([
            'title' => __('Settings saved'),
            'description' => __('Your settings have been updated successfully.'),
            'type' => 'success',
        ]);

        return back();
    }
}
