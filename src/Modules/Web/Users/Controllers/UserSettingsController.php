<?php

declare(strict_types=1);

namespace Modules\Web\Users\Controllers;

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\AppearanceSettings;
use Domain\Users\DataObjects\GeneralSettings;
use Domain\Users\DataObjects\PlayerSettings;
use Domain\Users\DataObjects\UserSettings;
use Domain\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class UserSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Request $request, UserSettings $settings, #[CurrentUser] User $user): Response|RedirectResponse
    {
        Gate::authorize('update', $user);

        // Only keep the fields that were sent, so the defaults of the data objects don't overwrite
        // the user's other settings. Groups that weren't sent are filtered out as well.
        $update = array_filter([
            'player' => $settings->player instanceof PlayerSettings ? $this->sentFields($request, 'player', $settings->player->toArray()) : null,
            'general' => $settings->general instanceof GeneralSettings ? $this->sentFields($request, 'general', $settings->general->toArray()) : null,
            'appearance' => $settings->appearance instanceof AppearanceSettings ? $this->sentFields($request, 'appearance', $settings->appearance->toArray()) : null,
        ]);

        // Update the user's settings with the provided values.
        (new UpdateUserSettings)->handle($user, $update);

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        toast(title: __('Settings saved'), description: __('Your settings have been updated successfully.'));

        return back();
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function sentFields(Request $request, string $group, array $values): array
    {
        return array_intersect_key($values, (array) $request->input($group, []));
    }
}
