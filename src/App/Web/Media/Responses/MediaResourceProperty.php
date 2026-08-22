<?php

declare(strict_types=1);

namespace App\Web\Media\Responses;

use App\Api\Media\Resources\MediaResource;
use Domain\Media\Models\Media;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class MediaResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Media $media = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?MediaResource => $this->getResource());
    }

    protected function getResource(): ?MediaResource
    {
        if (! $this->media) {
            return null;
        }

        return $this->media
            ->loadMissing('model')
            ->toResource(MediaResource::class);
    }
}
