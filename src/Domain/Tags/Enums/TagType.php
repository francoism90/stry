<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

enum TagType: string
{
    case Serie = 'serie';
    case Studio = 'studio';
    case Genre = 'genre';
    case Person = 'person';
    case Language = 'language';

    public function label(): string
    {
        return match ($this) {
            self::Serie => __('Serie'),
            self::Studio => __('Studio'),
            self::Genre => __('Genre'),
            self::Person => __('Person'),
            self::Language => __('Language'),
        };
    }
}
