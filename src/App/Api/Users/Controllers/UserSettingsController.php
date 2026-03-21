<?php

declare(strict_types=1);

namespace App\Api\Users\Controllers;

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\UserSettings;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class UserSettingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Request $request, UserSettings $settings): RedirectResponse|Response
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

        return response()->noContent();
    }
}
