<?php

declare(strict_types=1);

namespace App\Web\Media\Responses;

use App\Api\Media\Resources\MediaResource;
use Domain\Media\Models\Media;
use Domain\Media\QueryBuilders\MediaQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class MediaQueryCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly Model $model,
        protected readonly ?int $limit = null,
        protected readonly ?int $page = 1,
        protected readonly ?int $perPage = 24,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return $this->model->media()
            ->ordered()
            ->when($this->limit,
                fn (MediaQueryBuilder $query, int $limit) => $query
                    ->take($limit)
                    ->get()
                    ->toResourceCollection(MediaResource::class),
                fn (MediaQueryBuilder $query) => $query
                    ->simplePaginate(perPage: $this->perPage, page: $this->page ?? 1)
                    ->through(fn (Media $media) => MediaResource::make($media))
            );
    }
}
