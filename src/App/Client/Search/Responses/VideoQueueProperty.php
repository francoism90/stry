<?php

declare(strict_types=1);

namespace App\Client\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Actions\GetSimilarVideos;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoQueueProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected Video $video,
        protected ?int $limit = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return ['data' => $this->resolveQueue()];
    }

    protected function resolveQueue(): array
    {
        return once(fn () => app(GetSimilarVideos::class)
            ->handle(video: $this->video, limit: $this->limit)
            ->loadMissing('tags')
            ->toResourceCollection(VideoResource::class)
            ->all());
    }
}
