<?php

declare(strict_types=1);

namespace Domain\Chapters\Concerns;

use Domain\Chapters\Models\Chapter;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait InteractsWithChapters
{
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
}
