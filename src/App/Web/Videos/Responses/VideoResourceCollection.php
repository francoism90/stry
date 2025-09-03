<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Collections\VideoCollection;
use Illuminate\Support\Collection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoResourceCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly Collection|array|null $items = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return VideoCollection::make($this->items)
            ->loadMissing('tags')
            ->toResourceCollection(VideoResource::class);
    }
}
