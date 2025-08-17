<?php

declare(strict_types=1);

namespace Domain\Groups\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MarkAsViewed
{
    public function handle(Model $model, ?User $user = null, ?array $attributes = null): mixed
    {
        return DB::transaction(function () use ($model, $user, $attributes) {
            if (! $user) {
                return;
            }

            // Ensure the viewed user group exists
            $group = app(CreateUserGroup::class)->handle($user, GroupType::Viewed);

            // Add the video to the user viewed group
            $model->syncGroup($group, $attributes);
        });
    }
}
