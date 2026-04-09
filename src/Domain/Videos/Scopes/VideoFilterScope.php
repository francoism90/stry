<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Groups\Models\Group;
use Domain\Profiles\Models\Profile;
use Domain\Tags\Models\Tag;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Laravel\Scout\Builder;

readonly class VideoFilterScope
{
    public function __construct(
        public Tag|string|null $tag = null,
        public Group|string|null $group = null,
        public VideoSorter|string|null $sort = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        // Get options based on current filter and user
        $options = $this->getOptions();

        // Determine if we should use placeholder results
        $defaultOrder = $this->isOrderDefault() && (blank($scout->query) || $scout->query === '*');

        $scout
            ->query(fn (VideoQueryBuilder $query) => $query->with('media', 'tags')->forProfile(Profile::current()))
            ->when($options, fn (Builder $scout) => $scout->options($options))
            ->when($defaultOrder, fn (Builder $scout) => $scout->randomOrder())
            ->when($this->getTag(), fn (Builder $scout, Tag $tag) => $scout->whereIn('tagged', [$tag->getKey()]))
            ->when($this->isOrder(VideoSorter::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isOrder(VideoSorter::Ordered), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isOrder(VideoSorter::Shortest), fn (Builder $scout) => $scout->orderBy('duration'))
            ->when($this->isOrder(VideoSorter::Longest), fn (Builder $scout) => $scout->orderByDesc('duration'))
            ->when($this->isOrder(VideoSorter::Filesize), fn (Builder $scout) => $scout->orderByDesc('filesize'));
    }

    protected function getOptions(): array
    {
        // Initialize options array
        $options = [];

        if ($group = $this->getGroup()) {
            // Make sure the group has videos, otherwise return no results
            if ($group->videos()->doesntExist()) {
                // Return no results
                $options['filter_by'] = 'id:0';

                return $options;
            }

            // Set filter by group ID
            $options['filter_by'] = sprintf('$groupables(group_id:%d)', $group->getKey());

            // Set default sorting for certain groups
            if ($this->isOrderDefault()) {
                $options['sort_by'] = '$groupables(updated_at:desc)';
            }
        }

        return $options;
    }

    protected function getOrderer(): VideoSorter
    {
        $sortValue = $this->sort ?? VideoSorter::Default;

        return is_string($sortValue) ? VideoSorter::from($sortValue) : $sortValue;
    }

    protected function isOrderDefault(): bool
    {
        return $this->getOrderer() === VideoSorter::Default;
    }

    protected function isOrder(VideoSorter ...$values): bool
    {
        $currentOrderer = $this->getOrderer();

        return $currentOrderer && in_array($currentOrderer, $values, true);
    }

    protected function getGroup(): ?Group
    {
        if (! $this->group) {
            return null;
        }

        return Group::findFromUlid($this->group);
    }

    protected function getTag(): ?Tag
    {
        if (! $this->tag) {
            return null;
        }

        return Tag::findFromUlid($this->tag);
    }
}
