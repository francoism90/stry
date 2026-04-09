<?php

declare(strict_types=1);

namespace Domain\Profiles\QueryBuilders;

use Domain\Profiles\Enums\ProfileSorter;
use Domain\Profiles\States;
use Illuminate\Database\Eloquent\Builder;

class ProfileQueryBuilder extends Builder
{
    public function enabled(): self
    {
        return $this->whereState('state', States\Enabled::class);
    }

    public function disabled(): self
    {
        return $this->whereState('state', States\Disabled::class);
    }

    public function ordered(ProfileSorter|string|null $order = null): self
    {
        $orderValue = $order ?? ProfileSorter::Name;

        $orderer = is_string($orderValue) ? ProfileSorter::from($orderValue) : $orderValue;

        return $this
            ->orderByDesc('is_primary')
            ->when($orderer === ProfileSorter::Newest, fn (self $query) => $query->orderByDesc('created_at'))
            ->when($orderer === ProfileSorter::Oldest, fn (self $query) => $query->orderBy('created_at'))
            ->when($orderer === ProfileSorter::Name, fn (self $query) => $query->orderBy('name'));
    }

    public function current(): self
    {
        return $this
            ->enabled()
            ->ordered();
    }
}
