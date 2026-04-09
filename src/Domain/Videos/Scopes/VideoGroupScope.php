<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Groups\Models\Group;
use Laravel\Scout\Builder;

readonly class VideoGroupScope
{
    public function __construct(
        public Group|string|null $group = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        // Initialize options array
        $options = [];

        // Set default filter to return no results if no group is provided
        $options['filter_by'] = 'id:0';

        // Get group (if available)
        $group = $this->getGroup();

        // If group exists and has videos, set filter to return videos in the group
        if ($group && $group->videos()->exists()) {
            $options['filter_by'] = sprintf('$groupables(group_id:%d)', $group->getKey());
            $options['sort_by'] = '$groupables(updated_at:desc)';
        }

        // Apply options to the scout builder
        $scout->options($options);
    }

    protected function getGroup(): ?Group
    {
        if (! $this->group) {
            return null;
        }

        return Group::findFromUlid($this->group);
    }
}
