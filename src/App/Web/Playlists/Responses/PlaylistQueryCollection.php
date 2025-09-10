<?php

declare(strict_types=1);

namespace App\Web\Playlists\Responses;

use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\QueryBuilders\PlaylistQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class PlaylistQueryCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly Model $model,
        protected readonly ?int $limit = null,
        protected readonly ?int $page = 1,
        protected readonly ?int $perPage = 24,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return $this->model->playlists()
            ->ordered()
            ->when($this->limit,
                fn (PlaylistQueryBuilder $query, int $limit) => $query
                    ->take($limit)
                    ->get()
                    ->toResourceCollection(PlaylistResource::class),
                fn (PlaylistQueryBuilder $query) => $query
                    ->simplePaginate(perPage: $this->perPage, page: $this->page ?? 1)
                    ->through(fn (Playlist $playlist) => PlaylistResource::make($playlist))
            );
    }
}
