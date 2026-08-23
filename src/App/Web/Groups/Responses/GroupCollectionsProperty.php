<?php

declare(strict_types=1);

namespace App\Web\Groups\Responses;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Profiles\Models\Profile;
use Domain\Users\Models\User;
use Illuminate\Support\Collection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class GroupCollectionsProperty implements ProvidesInertiaProperty
{
    /**
     * @var array<int, GroupType>
     */
    protected const array PINNED_TYPES = [
        GroupType::Liked,
        GroupType::Saved,
        GroupType::Viewed,
    ];

    protected const int CUSTOM_LIMIT = 20;

    public function __construct(
        protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn () => $this->getCollections());
    }

    /** @return Collection<int, array{id: string, name: string, title: string, type: string}> */
    protected function getCollections(): Collection
    {
        $userId = Profile::current()?->user_id ?? $this->user?->id;

        if (blank($userId)) {
            return Collection::empty();
        }

        $pinned = Group::query()
            ->where('user_id', $userId)
            ->whereIn('type', self::PINNED_TYPES)
            ->get()
            ->sortBy(fn (Group $group): int => array_search($group->type, self::PINNED_TYPES, strict: true));

        $custom = Group::query()
            ->where('user_id', $userId)
            ->where('type', GroupType::Custom)
            ->orderBy('name')
            ->limit(self::CUSTOM_LIMIT)
            ->get();

        return $pinned
            ->merge($custom)
            ->values()
            ->map(fn (Group $group): array => [
                'id' => $group->getRouteKey(),
                'name' => (string) $group->name,
                'title' => (string) $group->title,
                'type' => $group->type->value,
            ]);
    }
}
