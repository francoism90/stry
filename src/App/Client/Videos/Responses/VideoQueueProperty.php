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
        return once(fn (): array => ['data' => $this->getCollection()]);
    }

    protected function getCollection(): array
    {
        return  app(GetSimilarVideos::class)
            ->handle(video: $this->video, limit: $this->limit)
            ->loadMissing('tags')
            ->toResourceCollection(VideoResource::class)
            ->all();
    }
}
