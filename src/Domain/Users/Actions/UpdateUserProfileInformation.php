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
        logger($input);

        $request = new UserUpdateRequest;
        $request->setRouteResolver(fn () => request()->route());
        $request->merge(['user' => $user]);

        Validator::make($input, $request->rules())
            ->validateWithBag('updateProfileInformation');

        logger('validation passed');

        DB::transaction(function () use ($user, $input) {
            if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
                $this->updateVerifiedUser($user, $input);

                return;
            }

            logger($input);

            // Update user attributes
            $user->updateOrFail(
                Arr::only($input, $user->getFillable()),
            );
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
