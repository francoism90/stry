<?php

declare(strict_types=1);

namespace Domain\Profiles\QueryBuilders;

use Domain\Profiles\Enums\ProfileOrder;
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

    public function ordered(ProfileOrder|string|null $order = null): self
    {
        $orderValue = $order ?? ProfileOrder::Name;
        $orderer = is_string($orderValue) ? ProfileOrder::from($orderValue) : $orderValue;

        return $this
            ->orderByDesc('is_primary')
            ->when($orderer === ProfileOrder::Newest, fn (self $query) => $query->orderByDesc('created_at'))
            ->when($orderer === ProfileOrder::Name, fn (self $query) => $query->orderBy('name'));
    }

    public function current(): self
    {
        return $this
            ->enabled()
            ->ordered();
    }
}
