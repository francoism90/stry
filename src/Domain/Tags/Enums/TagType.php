<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

enum TagType: string
{
    case Serie = 'serie';
    case Studio = 'studio';
    case Person = 'person';
    case Genre = 'genre';
    case Language = 'language';

    public function label(): string
    {
        return match ($this) {
            self::Serie => __('Serie'),
            self::Studio => __('Studio'),
            self::Person => __('Person'),
            self::Genre => __('Genre'),
            self::Language => __('Language'),
        };
    }
}
