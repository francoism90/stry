<?php

declare(strict_types=1);

namespace Domain\Chapters\Concerns;

use Domain\Chapters\Models\Chapter;
use Foxws\Media\Delivery\Marker;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait InteractsWithChapters
{
    /**
     * @return HasMany<Chapter, $this>
     */
    public function chapters(): HasMany
    {
        return $this
            ->hasMany(Chapter::class)
            ->ordered()
            ->orderBy('start_time');
    }

    public function getSkippableChapterAt(float $time): ?Chapter
    {
        return $this->chapters->first(
            fn (Chapter $chapter) => $chapter->type->isSkippable()
                && $time >= $chapter->start_time
                && $time < $chapter->end_time,
        );
    }

    /**
     * @return list<Marker>
     */
    public function getChapterMarkers(): array
    {
        return $this->chapters
            ->filter(fn (Chapter $chapter) => (float) $chapter->start_time >= 0 && (float) $chapter->end_time >= (float) $chapter->start_time)
            ->map(fn (Chapter $chapter) => new Marker(
                start: (float) $chapter->start_time,
                end: (float) $chapter->end_time,
                title: $chapter->label,
                class: $chapter->type->value,
            ))
            ->values()
            ->all();
    }
}
