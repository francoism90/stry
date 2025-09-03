<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Laravel\Scout\Builder;

readonly class VideoScoutCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly ?string $query = null,
        protected readonly ?string $sort = null,
        protected readonly ?array $tags = null,
        protected readonly ?int $limit = null,
        protected readonly ?int $page = 1,
        protected readonly ?int $perPage = 24,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return Video::search($this->query ?? '')
            ->query(fn (VideoQueryBuilder $query) => $query->verified()->with('tags'))
            ->when(blank($this->query), fn (Builder $query) => $query->where('id', 0))
            ->when($this->tags, fn (Builder $query, array $tags) => $query->whereIn('tagged', $tags))
            ->when($this->sort === 'ordered', fn (Builder $query) => $query->orderBy('name'))
            ->when($this->sort === 'longest', fn (Builder $query) => $query->orderByDesc('duration'))
            ->when($this->sort === 'shortest', fn (Builder $query) => $query->orderBy('duration'))
            ->when($this->limit,
                fn (Builder $query, int $limit) => $query
                    ->take($limit)
                    ->get()
                    ->toResourceCollection(VideoResource::class),
                fn (Builder $query) => $query
                    ->simplePaginate(perPage: $this->perPage, page: $this->page ?? 1)
                    ->through(fn (Video $video) => VideoResource::make($video))
            );
    }
}
