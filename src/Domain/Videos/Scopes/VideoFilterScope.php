<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Groups\Models\Group;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Domain\Videos\Enums\VideoFilter;
use Domain\Videos\Enums\VideoOrder;
use Laravel\Scout\Builder;

readonly class VideoFilterScope
{
    public function __construct(
        public User|string|null $user = null,
        public Tag|string|null $tag = null,
        public VideoFilter|string|null $filter = null,
        public VideoOrder|string|null $order = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        // Get options based on current filter and user
        $options = $this->getOptions();

        // Determine if we should use placeholder results
        $defaultOrder = $this->isDefault() && (blank($scout->query) || $scout->query === '*');

        $scout
            ->when($options, fn (Builder $scout) => $scout->options($options))
            ->when($defaultOrder, fn (Builder $scout) => $scout->randomOrder())
            ->when($this->getTag(), fn (Builder $scout, Tag $tag) => $scout->whereIn('tagged', [$tag->getKey()]))
            ->when($this->isOrder(VideoOrder::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isOrder(VideoOrder::Ordered), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isOrder(VideoOrder::Shortest), fn (Builder $scout) => $scout->orderBy('duration'))
            ->when($this->isOrder(VideoOrder::Longest), fn (Builder $scout) => $scout->orderByDesc('duration'));
    }

    protected function getOptions(): array
    {
        // Get the current user (if any)
        $user = $this->getUser();

        // Initialize options array
        $options = [];

        // Build group options
        $group = match ($this->getFilter()) {
            VideoFilter::Liked => $user?->likedGroup(),
            VideoFilter::Saved => $user?->savedGroup(),
            VideoFilter::History => $user?->viewedGroup(),
            default => null,
        };

        if ($group instanceof Group) {
            // Make sure the group has videos
            if ($group->videos()->doesntExist()) {
                // Return no results
                $options['filter_by'] = 'id:0';

                return $options;
            }

            // Set filter by group ID
            $options['filter_by'] = sprintf('$groupables(group_id:%d)', $group->getKey());

            // Set default sorting for certain filters
            if ($this->isOrderDefault()) {
                $options['sort_by'] = '$groupables(updated_at:desc)';
            }
        }

        return $options;
    }

    protected function getFilter(): VideoFilter
    {
        $filterValue = $this->filter ?? VideoFilter::Default;

        return is_string($filterValue) ? VideoFilter::from($filterValue) : $filterValue;
    }

    protected function getOrderer(): VideoOrder
    {
        $orderValue = $this->order ?? VideoOrder::Default;

        return is_string($orderValue) ? VideoOrder::from($orderValue) : $orderValue;
    }

    protected function isDefault(): bool
    {
        return $this->isFilterDefault() && $this->isOrderDefault();
    }

    protected function isFilterDefault(): bool
    {
        return $this->getFilter() === VideoFilter::Default;
    }

    protected function isOrderDefault(): bool
    {
        return $this->getOrderer() === VideoOrder::Default;
    }

    protected function isFilter(VideoFilter ...$values): bool
    {
        $currentFilter = $this->getFilter();

        return in_array($currentFilter, $values, true);
    }

    protected function isOrder(VideoOrder ...$values): bool
    {
        $currentOrderer = $this->getOrderer();

        return $currentOrderer && in_array($currentOrderer, $values, true);
    }

    protected function getTag(): ?Tag
    {
        if (! $this->tag) {
            return null;
        }

        return Tag::findFromUlid($this->tag);
    }

    protected function getUser(): ?User
    {
        if (! $this->user) {
            return null;
        }

        return User::findFromUlid($this->user);
    }
}
