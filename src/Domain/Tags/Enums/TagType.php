<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

enum TagType: string
{
    case Genre = 'genre';
    case Serie = 'serie';
    case Person = 'person';
    case Studio = 'studio';
    case Language = 'language';

    public function label(): string
    {
        return match ($this) {
            self::Genre => __('Genre'),
            self::Serie => __('Serie'),
            self::Person => __('Person'),
            self::Studio => __('Studio'),
            self::Language => __('Language'),
        };
    }
}
