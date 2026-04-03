<?php

declare(strict_types=1);

namespace Domain\Profiles\Actions;

use Domain\Profiles\Models\Profile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateProfileDetails
{
    public function handle(Profile $profile, array $attributes = []): void
    {
        DB::transaction(function () use ($profile, $attributes): void {
            $profile->updateOrFail(
                Arr::only($attributes, $profile->getFillable()),
            );

            if ((bool) ($attributes['is_primary'] ?? false)) {
                $profile
                    ->newQuery()
                    ->where('user_id', $profile->user_id)
                    ->whereKeyNot($profile->getKey())
                    ->update(['is_primary' => false]);
            }
        });
    }
}
