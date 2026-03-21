<?php

declare(strict_types=1);

namespace App\Api\Users\Controllers;

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\UserSettings;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserSettingsController extends Controller
{
    public function __invoke(Request $request, UserSettings $settings): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $request->user());

        // Only include settings that were actually sent in the request
        $update = array_filter([
            'player' => $settings->player?->toArray(),
            'general' => $settings->general?->toArray(),
            'appearance' => $settings->appearance?->toArray(),
        ]);

        // Update the user's settings with the provided values
        (new UpdateUserSettings)->handle($request->user(), $update);

        if ($request->inertia()) {
            Inertia::flash([
                'title' => __('Settings updated'),
                'description' => __('Your settings have been successfully updated.'),
            ]);

            return back();
        }

        return response()->json();
    }
}
