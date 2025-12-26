<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Users\Models\User;
use Domain\Videos\Enums\VideoFilter;
use Domain\Videos\Enums\VideoOrder;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Request;
use Laravel\Scout\Builder;

readonly class VideoFilterScope
{
    public function __construct(
        public ?User $user = null,
        public VideoFilter|string|null $filter = null,
        public VideoOrder|string|null $order = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isDefault() && blank($scout->query), fn (Builder $scout) => $scout->randomOrder($this->randomSeed()))
            ->when($this->isFilter(VideoFilter::History), fn (Builder $scout) => $this->applyFilter($scout, VideoFilter::History))
            ->when($this->isFilter(VideoFilter::Liked), fn (Builder $scout) => $this->applyFilter($scout, VideoFilter::Liked))
            ->when($this->isFilter(VideoFilter::Saved), fn (Builder $scout) => $this->applyFilter($scout, VideoFilter::Saved))
            ->when($this->isOrder(VideoOrder::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isOrder(VideoOrder::Ordered), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isOrder(VideoOrder::Longest), fn (Builder $scout) => $scout->orderByDesc('duration'))
            ->when($this->isOrder(VideoOrder::Shortest), fn (Builder $scout) => $scout->orderBy('duration'));
    }

    protected function applyFilter(Builder $scout, VideoFilter $filter): Builder
    {
        // If no user is set, return the scout builder unmodified
        if (! $user = $this->getUser()) {
            return $scout;
        }

        return match ($filter) {
            VideoFilter::Liked => $scout->query(fn ($query) => $query->likedBy($user)),
            VideoFilter::History => $scout->query(fn ($query) => $query->viewedBy($user)),
            VideoFilter::Saved => $scout->query(fn ($query) => $query->savedBy($user)),
            default => $scout,
        };
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

    protected function getUser(): ?User
    {
        return $this->user;
    }

    protected function randomSeed(): int
    {
        /** @var Repository $sessionCache */
        $sessionCache = Request::session()->cache();

        return $sessionCache->remember(
            'video:random-seed',
            now()->addHour(),
            fn (): int => random_int(1, 1000)
        );
    }
}
