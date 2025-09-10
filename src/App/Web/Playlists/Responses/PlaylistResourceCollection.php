<?php

declare(strict_types=1);

namespace App\Web\Playlists\Responses;

use App\Api\Playlists\Resources\PlaylistResource;
use Illuminate\Support\Collection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class PlaylistResourceCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly Collection|array|null $items = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return PlaylistResource::make($this->items);
    }
}
