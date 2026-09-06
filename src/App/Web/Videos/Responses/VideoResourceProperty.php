<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Video $video = null,
        protected ?array $appends = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?VideoResource => $this->getResource());
    }

    protected function getResource(): ?VideoResource
    {
        if (! $this->video) {
            return null;
        }

        return $this->video
            ->loadMissing('media', 'tags', 'user', 'chapters')
            ->append($this->appends ?? [])
            ->toResource(VideoResource::class);
    }
}
