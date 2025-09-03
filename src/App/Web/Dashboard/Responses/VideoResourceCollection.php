<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoResourceCollection implements ProvidesInertiaProperty
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?int $limit = null,
        public readonly ?int $page = null,
        public readonly ?int $perPage = 16,
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
