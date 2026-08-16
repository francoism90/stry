<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TagType: string implements Enumerable
{
    case All = 'all';
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
            'all' => __('All'),
            'serie' => __('Serie'),
            'studio' => __('Studio'),
            'genre' => __('Genre'),
            'person' => __('Person'),
            'language' => __('Language'),
        ];
    }
}
