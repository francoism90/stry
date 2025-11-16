<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

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
        // Fill in the video queue using similar videos
        $candidates = (new GetSimilarVideos)->handle(video: $this->video, limit: $this->limit)
            ->loadMissing('tags')
            ->toResourceCollection(VideoResource::class);

        return ['data' => $candidates->all()];
    }
}
