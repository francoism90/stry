<?php

declare(strict_types=1);

namespace App\Api\Users\Controllers;

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\UserSettings;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function __invoke(Request $request, UserSettings $settings): JsonResponse
    {
        $update = array_filter([
            'player' => $settings->player?->toArray(),
            'general' => $settings->general?->toArray(),
            'appearance' => $settings->appearance?->toArray(),
        ]);

        (new UpdateUserSettings)->handle($request->user(), $update);

        return response()->json();
    }
}
