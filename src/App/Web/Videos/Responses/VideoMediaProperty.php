<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Media\Resources\MediaResource;
use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Gate;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoMediaProperty implements ProvidesInertiaProperty
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
        if (! $this->video || Gate::denies('viewAny', Media::class)) {
            return MediaResource::collection([]);
        }

        return $this->video
            ->media()
            ->latest()
            ->limit($this->limit ?? 10)
            ->get()
            ->each(fn (Media $item) => $item->append(['custom_properties', 'generated_conversions']))
            ->toResourceCollection(MediaResource::class);
    }
}
