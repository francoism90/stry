<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoList: string implements Enumerable
{
    case Recommended = 'recommended';
    case Shorts = 'shorts';
    case Watched = 'watched';
    case Newest = 'newest';

    public function label(): string
    {
        return match ($this) {
            self::Recommended => __('Recommended'),
            self::Shorts => __('Shorts'),
            self::Watched => __('Watched'),
            self::Newest => __('Newest'),
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
