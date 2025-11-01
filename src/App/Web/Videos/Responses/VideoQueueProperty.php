<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Actions\GetVideoQueue;
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
        $data = app(GetVideoQueue::class)->handle($this->video, $this->limit)
            ->loadMissing('tags')
            ->toResourceCollection(VideoResource::class);

        return compact('data');
    }
}
