<?php

declare(strict_types=1);

namespace Domain\Videos\Filters;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Profiles\Models\Profile;
use Domain\Videos\Enums\VideoScope;
use Foxws\ScoutBuilder\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Builder;

class VideoScopeFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        match (VideoScope::tryFrom($value)) {
            VideoScope::Shorts => $this->applyShorts($query),
            VideoScope::Unseen => $this->applyUnseen($query),
            VideoScope::Untagged => $this->applyUntagged($query),
            default => null,
        };
    }

    private function applyShorts(Builder $query): void
    {
        $query->where('duration', '<=', 300);
    }

    private function applyUntagged(Builder $query): void
    {
        $query->where('tagged_count', 0);
    }

    private function applyUnseen(Builder $query): void
    {
        // Get the current user's ID, either from the current profile or the authenticated user
        $userId = Profile::current()?->user_id ?? Auth::id();

        if (blank($userId)) {
            return;
        }

        // Find the "viewed" group for the user, which contains videos they've seen (if any).
        $group = Group::query()
            ->where('user_id', $userId)
            ->where('type', GroupType::Viewed)
            ->first();

        if (! $group) {
            return;
        }

        $previousCallback = $query->callback;

        $query->callback = function ($typesense, $scoutQuery, $options) use ($group, $previousCallback) {
            $options['filter_by'] = filled($options['filter_by'] ?? '')
                ? sprintf('%s && $groupables(group_id:!=%d)', $options['filter_by'], $group->getKey())
                : sprintf('$groupables(group_id:!=%d)', $group->getKey());

            if ($previousCallback) {
                return $previousCallback($typesense, $scoutQuery, $options);
            }

            return $typesense->search($options);
        };
    }
}
