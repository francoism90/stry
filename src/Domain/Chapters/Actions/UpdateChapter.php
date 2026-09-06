<?php

declare(strict_types=1);

namespace Domain\Chapters\Actions;

use Domain\Chapters\Models\Chapter;

class UpdateChapter
{
    public function __construct(
        protected ClassifyChapterType $classifyChapterType,
    ) {}

    public function handle(Chapter $chapter, array $attributes): Chapter
    {
        if (blank($attributes['type'] ?? null) && filled($attributes['label'] ?? null)) {
            $attributes['type'] = $this->classifyChapterType->handle($attributes['label'])->value;
        }

        $chapter->updateOrFail($attributes);

        return $chapter;
    }
}
