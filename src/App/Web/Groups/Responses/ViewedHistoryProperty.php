<?php

declare(strict_types=1);

namespace App\Web\Groups\Responses;

use Domain\Groups\Models\Group;
use Domain\Profiles\Models\Profile;
use Domain\Users\Models\User;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class ViewedHistoryProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn () => $this->getViewedHistory());
    }

    /** @return array{id: string, name: string, title: string, type: string}|null */
    protected function getViewedHistory(): ?array
    {
        $userId = Profile::current()?->user_id ?? $this->user?->id;

        if (blank($userId)) {
            return null;
        }

        $group = Group::query()
            ->viewed()
            ->where('user_id', $userId)
            ->first();

        if (! $group) {
            return null;
        }

        return [
            'id' => $group->getRouteKey(),
            'name' => (string) $group->name,
            'title' => (string) $group->title,
            'type' => $group->type->value,
        ];
    }
}
