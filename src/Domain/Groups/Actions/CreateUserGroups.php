<?php

declare(strict_types=1);

namespace Domain\Groups\Actions;

use Closure;
use Domain\Groups\Enums\GroupSet;
use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;

class CreateUserGroups
{
    public function __invoke(User $user, Closure $next): mixed
    {
        collect($this->getGroups())->each(
            fn (GroupSet $group) => app(CreateNewGroup::class)->execute($user, [
                'name' => $group->label(),
                'kind' => $group,
                'type' => GroupType::System,
            ])
        );

        return $next($user);
    }

    protected function getGroups(): array
    {
        return [
            GroupSet::Favorite,
            GroupSet::Saved,
            GroupSet::Viewed,
        ];
    }
}
