<?php

declare(strict_types=1);

namespace Support\Scout\Filters;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Profiles\Models\Profile;
use Foxws\ScoutBuilder\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Builder;

class FiltersUnseen implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        // Get the current user's ID, either from the current profile or the authenticated user
        $userId = Profile::current()?->user_id ?? Auth::id();

        if (blank($userId)) {
            return;
        }

        // Find the "viewed" group for the user, which contains videos they've seen (if any).
        $viewedGroup = Group::query()
            ->where('user_id', $userId)
            ->where('type', GroupType::Viewed)
            ->first();

        if (! $viewedGroup) {
            return;
        }

        // Get the group ID and set up a custom callback to exclude videos in that group from the search results.
        $groupId = (string) $viewedGroup->getKey();

        $previousCallback = $query->callback;

        $query->callback = function ($typesense, $scoutQuery, $options) use ($groupId, $previousCallback) {
            $exclude = "\$exclude(groupables, groupables.group_id:={$groupId})";

            $options['filter_by'] = filled($options['filter_by'] ?? '')
                ? "{$options['filter_by']} && {$exclude}"
                : $exclude;

            if ($previousCallback) {
                return $previousCallback($typesense, $scoutQuery, $options);
            }

            return $typesense->search($options);
        };
    }
}
