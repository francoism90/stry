<?php

declare(strict_types=1);

namespace App\Client\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Actions\GetSimilarVideos;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoQueueProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Video $video = null,
        protected ?int $limit = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn () => $this->getCollection());
    }

    protected function getCollection(): ResourceCollection
    {
        if (! $this->video) {
            return VideoResource::collection([]);
        }

        return app(GetSimilarVideos::class)
            ->handle(video: $this->video, limit: $this->limit)
            ->loadMissing('tags')
            ->toResourceCollection(VideoResource::class);
    }
}
