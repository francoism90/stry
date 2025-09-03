<?php

declare(strict_types=1);

namespace App\Web\Tags\Responses;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class TagQueryCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly ?string $type = null,
        protected readonly ?int $limit = null,
        protected readonly ?int $page = 1,
        protected readonly ?int $perPage = 24,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return Tag::query()
            ->withCount('videos')
            ->when($this->type,
                fn (TagQueryBuilder $query, string $type) => $query->type($type)->ordered(),
                fn (TagQueryBuilder $query) => $query->inRandomOrder()
            )
            ->when($this->limit,
                fn (TagQueryBuilder $query, int $limit) => $query
                    ->take($limit)
                    ->get()
                    ->toResourceCollection(TagResource::class),
                fn (TagQueryBuilder $query) => $query
                    ->simplePaginate(perPage: $this->perPage, page: $this->page ?? 1)
                    ->through(fn (Tag $video) => TagResource::make($video))
            );
    }
}
