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
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'serie' => __('Serie'),
            'studio' => __('Studio'),
            'genre' => __('Genre'),
            'person' => __('Person'),
            'language' => __('Language'),
        ];
    }
}
