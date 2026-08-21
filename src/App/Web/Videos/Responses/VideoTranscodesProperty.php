<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Transcodes\Resources\TranscodeResource;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Gate;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoTranscodesProperty implements ProvidesInertiaProperty
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
        if (! $this->video || Gate::denies('viewAny', Transcode::class)) {
            return TranscodeResource::collection([]);
        }

        return $this->video
            ->transcodes()
            ->latest()
            ->limit($this->limit ?? 10)
            ->get()
            ->toResourceCollection(TranscodeResource::class);
    }
}
