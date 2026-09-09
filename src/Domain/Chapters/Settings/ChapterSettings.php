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
        'intro' => '/\b(intro(duction)?|leader|opening|op|teaser)\b/i',
        'recap' => '/\b(recap|previously|catch[- ]?up|preview|story so far)\b/i',
        'credits' => '/\b(credits?|end\s?card|outro|ed|closing)\b/i',
        'sponsor' => '/\b(sponsor(ed|s)?|advertisement|promo(tion)?)\b/i',
        'filler' => '/\bfiller\b/i',
        'interaction_reminder' => '/\b(like\s?(and|&)?\s?subscribe|subscribe reminder|interaction reminder)\b/i',
    ];

    public ChapterType $default_type = ChapterType::Scene;

    public static function group(): string
    {
        return 'chapters';
    }
}
