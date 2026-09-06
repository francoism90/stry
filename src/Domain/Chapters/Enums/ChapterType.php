<?php

declare(strict_types=1);

namespace Domain\Chapters\Enums;

use Domain\Shared\Contracts\Enumerable;

enum ChapterType: string implements Enumerable
{
    case Intro = 'intro';
    case Recap = 'recap';
    case Credits = 'credits';
    case Scene = 'scene';
    case MainEvent = 'main_event';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'intro' => __('Intro'),
            'recap' => __('Recap'),
            'credits' => __('Credits'),
            'scene' => __('Scene'),
            'main_event' => __('Main Event'),
        ];
    }

    public function isSkippable(): bool
    {
        return match ($this) {
            self::Intro, self::Recap, self::Credits => true,
            self::Scene, self::MainEvent => false,
        };
    }
}
