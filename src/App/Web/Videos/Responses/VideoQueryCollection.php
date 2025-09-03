<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoQueryCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly ?string $type = null,
        protected readonly ?int $limit = null,
        protected readonly ?int $page = 1,
        protected readonly ?int $perPage = 24,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return Video::query()
            ->verified()
            ->with('tags')
            ->when($this->type === null, fn (VideoQueryBuilder $query) => $query->inRandomOrder())
            ->when($this->type === 'newest', fn (VideoQueryBuilder $query) => $query->latest())
            ->when($this->type === 'watching', fn (VideoQueryBuilder $query) => $query->watching())
            ->when($this->limit,
                fn (VideoQueryBuilder $query, int $limit) => $query
                    ->take($limit)
                    ->get()
                    ->toResourceCollection(VideoResource::class),
                fn (VideoQueryBuilder $query) => $query
                    ->simplePaginate(perPage: $this->perPage, page: $this->page ?? 1)
                    ->through(fn (Video $video) => VideoResource::make($video))
            );
    }
}
