<?php

declare(strict_types=1);

namespace Domain\Chapters\Actions;

use Domain\Chapters\Models\Chapter;
use Domain\Videos\Models\Video;

class CreateChapter
{
    public function __construct(
        protected ClassifyChapterType $classifyChapterType,
    ) {}

    public function handle(Video $video, array $attributes): Chapter
    {
        if (blank($attributes['type'] ?? null) && filled($attributes['label'] ?? null)) {
            $attributes['type'] = $this->classifyChapterType->handle($attributes['label'])->value;
        }

        return $video->chapters()->create($attributes);
    }
}
