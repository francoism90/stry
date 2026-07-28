<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Modules\Videos\Resources\VideoResource;
use Domain\Videos\Actions\GetSimilarVideos;
use Domain\Videos\Models\Video;
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
        if (! $this->video) {
            return VideoResource::collection([]);
        }

        return app(GetSimilarVideos::class)
            ->handle(video: $this->video, limit: $this->limit ?? 16)
            ->loadMissing('tags')
            ->toResourceCollection(VideoResource::class);
    }
}
