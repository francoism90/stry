<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoOrder: string implements Enumerable
{
    case Recommended = 'recommended';
    case Watched = 'watched';
    case Newest = 'newest';
    case Ordered = 'ordered';
    case Longest = 'longest';
    case Shortest = 'shortest';

    public function label(): string
    {
        return match ($this) {
            self::Recommended => __('Recommended'),
            self::Watched => __('Watched'),
            self::Newest => __('Newest'),
            self::Ordered => __('Ordered'),
            self::Longest => __('Longest'),
            self::Shortest => __('Shortest'),
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
