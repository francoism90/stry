<?php

declare(strict_types=1);

namespace Domain\Chapters\Actions;

use Domain\Chapters\Enums\ChapterType;
use Illuminate\Support\Facades\Config;

class ClassifyChapterType
{
    public function handle(string $label): ChapterType
    {
        foreach (Config::array('chapters.patterns') as $type => $pattern) {
            if (preg_match($pattern, $label) === 1) {
                return ChapterType::from($type);
            }
        }

        return ChapterType::from(Config::string('chapters.default_type', 'scene'));
    }
}
