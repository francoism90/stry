<?php

declare(strict_types=1);

namespace Domain\Groups\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUserGroup
{
    public function handle(User $user, GroupType $type): Group
    {
        return DB::transaction(function () use ($user, $type) {
            return app(CreateNewGroup::class)->handle($user, [
                'type' => $type,
            ]);
        });
    }
}
