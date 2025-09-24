<?php

declare(strict_types=1);

namespace App\Web\Media\Responses;

use App\Api\Media\Resources\MediaResource;
use Illuminate\Support\Collection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class MediaResourceCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly Collection|array|null $items = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return MediaResource::make($this->items);
    }
}
