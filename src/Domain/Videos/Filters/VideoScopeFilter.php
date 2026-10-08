<?php

declare(strict_types=1);

namespace Domain\Videos\Filters;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Profiles\Models\Profile;
use Domain\Videos\Enums\VideoScope;
use Domain\Videos\Settings\PlaybackSettings;
use Foxws\ScoutBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Builder;

class VideoScopeFilter implements Filter
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        match (VideoScope::tryFrom($value)) {
            VideoScope::Watching => $this->applyWatching($query),
            VideoScope::Shorts => $this->applyShorts($query),
            VideoScope::Unseen => $this->applyUnseen($query),
            VideoScope::Untagged => $this->applyUntagged($query),
            VideoScope::Captioned => $this->applyCaptioned($query),
            default => null,
        };
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyShorts(Builder $query): void
    {
        $query->where('duration', '<=', 300);
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyUntagged(Builder $query): void
    {
        $query->where('tagged_count', 0);
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyCaptioned(Builder $query): void
    {
        $query->where('captioned', true);
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyWatching(Builder $query): void
    {
        $group = $this->viewedGroup();

        // Without a viewed group nothing has been started yet; no group has id 0, so this matches nothing
        $groupId = $group?->getKey() ?? 0;

        $threshold = app(PlaybackSettings::class)->completion_threshold;

        $this->addJoinFilter($query, sprintf('$groupables(group_id:=%d && progress:>0 && progress:<%s)', $groupId, $threshold));
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function applyUnseen(Builder $query): void
    {
        $group = $this->viewedGroup();

        if (! $group) {
            return;
        }

        $this->addJoinFilter($query, sprintf('$groupables(group_id:!=%d)', $group->getKey()));
    }

    /**
     * Find the "viewed" group of the current profile's user, or of the authenticated user, which contains the videos they've seen.
     */
    private function viewedGroup(): ?Group
    {
        $userId = Profile::current()->user_id ?? Auth::id();

        if (blank($userId)) {
            return null;
        }

        return Group::query()
            ->where('user_id', $userId)
            ->where('type', GroupType::Viewed)
            ->first();
    }

    /**
     * Typesense join filters can't be expressed as Scout wheres, so they are appended to the search options.
     *
     * @param  Builder<Model>  $query
     */
    private function addJoinFilter(Builder $query, string $filter): void
    {
        $previousCallback = $query->callback;

        $query->callback = function ($typesense, $scoutQuery, $options) use ($filter, $previousCallback) {
            $options['filter_by'] = filled($options['filter_by'] ?? '')
                ? sprintf('%s && %s', $options['filter_by'], $filter)
                : $filter;

            if ($previousCallback) {
                return $previousCallback($typesense, $scoutQuery, $options);
            }

            return $typesense->search($options);
        };
    }
}
