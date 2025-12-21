<?php

declare(strict_types=1);

namespace App\Client\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        #[RouteParameter('video')] protected Video $video,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): VideoResource => $this->getResource());
    }

    protected function getResource(): VideoResource
    {
        return $this->video
            ->loadMissing('media', 'tags', 'user')
            ->toResource(VideoResource::class);
    }
}
