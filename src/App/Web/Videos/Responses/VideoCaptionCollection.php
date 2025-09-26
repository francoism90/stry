<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Media\Resources\MediaResource;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoCaptionCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly Video $video,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return MediaResource::collection($this->video->getCaptionCollection());
    }
}
