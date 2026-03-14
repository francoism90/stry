<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TagType: string implements Enumerable
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

    public static function options(): array
    {
        return array_map(fn (self $type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ], self::cases());
    }
}
