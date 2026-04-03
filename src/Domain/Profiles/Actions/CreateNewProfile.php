<?php

declare(strict_types=1);

namespace Domain\Profiles\Actions;

use Domain\Profiles\Models\Profile;
use Domain\Profiles\States\Enabled;
use Domain\Users\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateNewProfile
{
    public function handle(User $user, array $attributes = []): Profile
    {
        return DB::transaction(function () use ($user, $attributes): Profile {
            $isFirstProfile = ! $user->profiles()->exists();

            $profile = $user->profiles()->create([
                ...Arr::only($attributes, (new Profile)->getFillable()),
                'state' => Enabled::class,
                'settings' => $attributes['settings'] ?? [],
                'is_primary' => $isFirstProfile || (bool) ($attributes['is_primary'] ?? false),
            ]);

            if ($profile->is_primary) {
                $user->profiles()
                    ->whereKeyNot($profile->getKey())
                    ->update(['is_primary' => false]);
            }

            return $profile;
        });
    }
}
