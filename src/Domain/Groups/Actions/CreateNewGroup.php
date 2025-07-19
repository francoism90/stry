<?php

declare(strict_types=1);

namespace Domain\Groups\Actions;

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateNewGroup
{
    public function execute(User $user, array $attributes): Group
    {
        return DB::transaction(function () use ($user, $attributes) {
            $model = $user->groups()->firstOrCreate(
                Arr::only($attributes, ['name', 'kind', 'type']),
                Arr::only($attributes, app(Group::class)->getFillable()),
            );

            return $model;
        });
    }
}
