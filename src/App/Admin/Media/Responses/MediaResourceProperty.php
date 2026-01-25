<?php

declare(strict_types=1);

namespace App\Admin\Media\Responses;

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

        // Append necessary attributes for the edit form
        $appends = [
            'custom_properties',
            'generated_conversions',
        ];

        return $this->media
            ->append($appends)
            ->toResource(MediaResource::class);
    }
}
