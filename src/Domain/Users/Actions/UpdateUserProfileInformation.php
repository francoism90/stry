<?php

declare(strict_types=1);

namespace Domain\Users\Actions;

use App\Api\Users\Requests\UserUpdateRequest;
use Domain\Users\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(User $user, array $input): void
    {
        $request = new UserUpdateRequest;
        $request->setRouteResolver(fn () => request()->route());

        Validator::make($input, $request->rules())
            ->validateWithBag('updateProfileInformation');

        DB::transaction(function () use ($user, $input) {
            if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
                $this->updateVerifiedUser($user, $input);

                return;
            }

            // Update user attributes
            $user->updateOrFail(
                Arr::only($input, $user->getFillable()),
            );

            // Sync settings if provided
            if (array_key_exists('settings', $input)) {
                app(UpdateUserSettings::class)->handle($user, $input['settings']);
            }
        });
    }

    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->saveOrFail();

        $user->sendEmailVerificationNotification();
    }
}
