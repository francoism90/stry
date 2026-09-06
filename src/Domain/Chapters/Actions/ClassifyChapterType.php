<?php

declare(strict_types=1);

namespace Domain\Chapters\Actions;

use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Settings\ChapterSettings;

class ClassifyChapterType
{
    public function __construct(
        protected ChapterSettings $settings,
    ) {}

    public function handle(string $label): ChapterType
    {
        foreach ($this->settings->patterns as $type => $pattern) {
            if (preg_match($pattern, $label) === 1) {
                return ChapterType::from($type);
            }
        }

        return $this->settings->default_type;
    }
}
