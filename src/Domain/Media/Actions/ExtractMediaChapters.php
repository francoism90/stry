<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Foxws\Media\MediaFactory;
use Foxws\Media\Probe\Chapter;
use Illuminate\Support\Collection;
use Throwable;

class ExtractMediaChapters
{
    public function __construct(
        protected MediaFactory $media,
    ) {}

    /**
     * @return Collection<int, array{label: string, start_time: float, end_time: float}>
     */
    public function handle(Media $media): Collection
    {
        try {
            $chapters = $this->media->fromDisk($media->disk)
                ->open($media->getPathRelativeToRoot())
                ->probe()
                ->chapters();
        } catch (Throwable) {
            return Collection::make();
        }

        return Collection::make($chapters)
            ->map(fn (Chapter $chapter): array => [
                'label' => (string) $chapter->title,
                'start_time' => $chapter->start,
                'end_time' => $chapter->end,
            ])
            ->filter(fn (array $chapter): bool => filled($chapter['label']) && $chapter['end_time'] > $chapter['start_time'])
            ->values();
    }
}
