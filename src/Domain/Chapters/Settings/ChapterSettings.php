<?php

declare(strict_types=1);

namespace Domain\Chapters\Settings;

use Domain\Chapters\Enums\ChapterType;
use Spatie\LaravelSettings\Settings;

class ChapterSettings extends Settings
{
    /**
     * @var array<string, string>
     */
    public array $patterns = [
        'intro' => '/\b(intro(duction)?|leader|opening)\b/i',
        'recap' => '/\b(recap|previously|catch[- ]?up)\b/i',
        'credits' => '/\b(credits?|end\s?card|outro)\b/i',
    ];

    public ChapterType $default_type = ChapterType::Scene;

    public static function group(): string
    {
        return 'chapters';
    }
}
