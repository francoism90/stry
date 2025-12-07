<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoList: string implements Enumerable
{
    case Recommended = 'recommended';
    case Shorts = 'shorts';
    case Newest = 'newest';
    case Watched = 'watched';

    public function label(): string
    {
        return match ($this) {
            self::Recommended => __('Recommended'),
            self::Shorts => __('Shorts'),
            self::Newest => __('Newest'),
            self::Watched => __('Watched'),
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
