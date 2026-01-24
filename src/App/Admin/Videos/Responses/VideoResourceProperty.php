<?php

declare(strict_types=1);

namespace App\Admin\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected Video $video,
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

        // Append necessary attributes for the edit form
        $appends = [
            'titles',
            'content',
            'summary',
            'snapshot',
        ];

        return $this->video
            ->loadMissing('media', 'tags', 'user')
            ->append($appends)
            ->toResource(VideoResource::class);
    }
}
