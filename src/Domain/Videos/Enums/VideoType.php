<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoType: string implements Enumerable
{
    case Newest = 'newest';
    case Longest = 'longest';
    case Shortest = 'shortest';
    case Watching = 'watching';

    public function label(): string
    {
        return match ($this) {
            self::Newest => __('Newest'),
            self::Longest => __('Longest'),
            self::Shortest => __('Shortest'),
            self::Watching => __('Watching'),
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
