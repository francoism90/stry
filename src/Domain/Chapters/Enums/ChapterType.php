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

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'intro' => 'Intro',
            'recap' => 'Recap',
            'credits' => 'Credits',
            'scene' => 'Scene',
        ];
    }

    public function isSkippable(): bool
    {
        return match ($this) {
            self::Intro, self::Recap, self::Credits => true,
            self::Scene => false,
        };
    }
}
