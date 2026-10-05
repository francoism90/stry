<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Responses;

use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Modules\Api\Videos\Resources\VideoDirectPlayResource;

readonly class VideoDirectPlayProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Video $video = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?VideoDirectPlayResource => $this->getDirectPlay());
    }

    protected function getDirectPlay(): ?VideoDirectPlayResource
    {
        if (! $this->video?->canDirectPlay()) {
            return null;
        }

        return VideoDirectPlayResource::make($this->video);
    }
}
